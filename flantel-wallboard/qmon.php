<?php 
/*
  qmon.php 
  Author: Barry Flanagan <barry AT flantel DOT com>
  Date: 21 Nov 2007
  
  Copyright 2007 Barry Flanagan, but feel free :-)
  
  Small script to grab the calls waiting and Agent information for a given queue from the QueueMetrics XML-RPC service and 
  create a simple display for use on a wallboard.
  
  Display colours will change from Green to Yellow to Red depending on Calls waiting, Ready Agents. Will also sound an alarm.
  
  This has proved useful in a large callcentre where the standard QM RT monitoring screen is too detailed and hard to see 
  from a distance. 
  
  To use, simply define the queues you want to monitor as $queueid, and set the QM ROBOT user and PASS, as well as the IP 
  address of your QM server

*/

// Set which queue you want to default to when none is specified 
$defaultqueue = 'verification';

// Allow seting of the queue to monitor, and the refresh time.
isset($_REQUEST['refresh'])?$refresh = $_REQUEST['refresh']:$refresh=120;
isset($_REQUEST['queue'])?$queuereq = $_REQUEST['queue']:$queuereq=$defaultqueue;

// Depending on which queue was requested, set $queueid for passing to queuemetrics.
switch ($queuereq) {
  case 'verification':
    $queueid = "301|310|311|312";
    $queuename='Dublin Verification';
    break ;;
  case 'inbound':
    $queueid = "401|402|403|404|405|406|407|408|409|410|411|412";
    $queuename='Inbound';
    break;;
  case 'outbound':
    $queueid = "Outbound";
    $queuename='Outbound';
    break ;;
}


// Set up the XML-RPC instance.
require_once 'XML/RPC.php';
$qm_server = "127.0.0.1"; // the QueueMetrics server address
$qm_port   = "8080";        // the port QueueMetrics is running on
$qm_webapp = "queuemetrics"; // the webapp name for QueueMetrics

$req_blocks_rt = new XML_RPC_Value(array(
                 new XML_RPC_Value("RealtimeDO.RTRiassunto"),
                 new XML_RPC_Value("RealtimeDO.RTCallsBeingProc"),
                 new  XML_RPC_Value("RealtimeDO.RTAgentsLoggedIn")
                 ), "array");
// general invocation parameters. Set the USER and PASSWORD to a QM user ith ROBOT key
$params_rt = array(
     new XML_RPC_Value($queueid),
           new XML_RPC_Value("USER"),
           new XML_RPC_Value("PASSWORD"),
           new XML_RPC_Value(""),
           new XML_RPC_Value(""),
           $req_blocks_rt
       );
$msg_rt = new XML_RPC_Message('QM.realtime', $params_rt);
$cli_rt = new XML_RPC_Client("/$qm_webapp/xmlrpc.do", $qm_server,$qm_port);
//$cli_rt->setDebug(1);
$resp_rt = $cli_rt->send($msg_rt);
if (!$resp_rt) {
    echo 'Communication error: ' . $cli_rt->errstr;
    exit;
}
if ($resp_rt->faultCode()) {
    echo 'Fault Code: ' . $resp_rt->faultCode() . "\n";
    echo 'Fault Reason: ' . $resp_rt->faultString() . "\n";
} else {
    $val_rt = $resp_rt->value();
    $blocks_rt = XML_RPC_decode($val_rt);

    // now we decode the results
    $queue = getQueueStatus( "RealtimeDO.RTRiassunto", $blocks_rt );
    $agent = getAgentLoggedIn( "RealtimeDO.RTAgentsLoggedIn", $blocks_rt );
    $agentStatus = getCurrentCalls( "RealtimeDO.RTCallsBeingProc", $blocks_rt, $agent );    

    // Get the waiting calls and number of agents for these queues
    $waitqueue = $queue['All selected']['waiting'];
    $nagents = $queue['All selected']['nagents'];
    $ragents = $queue['All selected']['ragents'];

}

// Get the waiting calls, logged in agents and available agents
function getQueueStatus( $blockname, $blocks ) {
     $queue = array();
     $block = $blocks[$blockname];
     for ( $r = 0; $r < sizeof( $block ) ; $r++ ) {
       $currentQueue = $block[$r][1];
       $queue[$currentQueue]['waiting'] =  $block[$r][7];
       $queue[$currentQueue]['nagents'] = $block[$r][2];
       $queue[$currentQueue]['ragents'] = $block[$r][3];
      }
    return $queue;
}

// Get the actual status iof each agent
function getAgentLoggedIn( $blockname, $blocks ) {
    $agent = array();
    $block = $blocks[$blockname];
     for ( $r = 1; $r < sizeof( $block ) ; $r++ ) {
       $agent[$block[$r][1]]['name'] = $block[$r][1];
       $agent[$block[$r][1]]['lastlogin'] = $block[$r][3];
       $agent[$block[$r][1]]['queues'] = $block[$r][4];
       $agent[$block[$r][1]]['extension'] = $block[$r][5];
       $agent[$block[$r][1]]['onpause'] = $block[$r][6];
       $agent[$block[$r][1]]['srv'] = $block[$r][7];
       $agent[$block[$r][1]]['lastcall'] = $block[$r][8];
       $agent[$block[$r][1]]['onqueue'] = $block[$r][9];
       $agent[$block[$r][1]]['caller'] = 'none';
       $agent[$block[$r][1]]['entered'] = null;
       $agent[$block[$r][1]]['waiting'] = null;
       $agent[$block[$r][1]]['duration'] = null;
      }
    return $agent;

}

// Get the current call details for each agent
function getCurrentCalls( $blockname, $blocks, $agent ) {
    global $agent;
    $block = $blocks[$blockname];
     for ( $r = 1; $r < sizeof( $block ) ; $r++ ) {
       $agent[$block[$r][6]]['caller'] = $block[$r][2];
       $agent[$block[$r][6]]['entered'] = $block[$r][3];
       $agent[$block[$r][6]]['waiting'] = $block[$r][4];
       $agent[$block[$r][6]]['duration'] = $block[$r][5];
      }
      $agentStatus = '';
      $rowcount=1;
      for  ( $r = 0; $r < sizeof( $agent ) ; $r++ ) {
        $agent = array_values($agent);
        if ($rowcount == 1){
          $rowcolor = "lightgray";
          $rowcount++;
        } elseif ($rowcount == 2) {
          $rowcolor = "gray";
          $rowcount = 1;
        }
        if ($agent[$r]['duration'] != '') {
          $status = "On Call since " . $agent[$r]['entered'] . " (" . $agent[$r]['duration'] . " mins)";
          $bgcolor3 = $rowcolor;
          $fontcolor3 = "red";
        } else {
          $status = "Available";
          $bgcolor3 = $rowcolor; 
          $fontcolor3 = "black";
        }
        $agentStatus .="<tr bgcolor=\"" . $rowcolor . "\" style=\"color:black; font-size: 20px;\"><td>&nbsp;</td><td>" . $agent[$r]['name'] . "</td><td style=\"color:" . $fontcolor3 . ";\" bgcolor=\"" . $bgcolor3 . "\" >" . $status .  "</td><td>" . $agent[$r]['lastcall'] .  "</td><td>" . $agent[$r]['caller'] . "</td></tr>";
      }
    return $agentStatus;
}

// Set up colour and formatting  depending on the status of the queue
if( $waitqueue == 0 ) {
    $bgcolor = "green";
    $snd = null;
    $fontcolor = "white";
  } elseif ($waitqueue == 1){
    $bgcolor = "yellow";
    $snd = '<EMBED SRC="/sounds/dingdong.wav" HIDDEN="true" AUTOSTART="true">';
    $fontcolor = "black";
  } elseif ($waitqueue > 1) {
    $bgcolor = "red";
    $snd = '<EMBED SRC="/sounds/ringer.wav" HIDDEN="true" AUTOSTART="true">';
    $fontcolor = "white";
    $waitqueue = "<blink>" . $waitqueue . "</blink>";
  }
  
// Set up colour and formatting  depending on the status of the agents for these queues
if( $ragents == 0 ) {
    $bgcolor2 = "red";
    $snd2 = '<img src="/sounds/snd4.wav">';
    $fontcolor2 = "white";
    $ragents = "<blink>" . $ragents . "</blink>";
  } elseif ($ragents <= 2){
    $bgcolor2 = "yellow";
    $snd2 = '<img src="/sounds/snd3.wav">';
    $fontcolor2 = "black";
  } elseif ($ragents > 2) {
    $bgcolor2 = "green";
    $snd2 = null;
    $fontcolor2 = "white";
  }

?>
<html><head><meta http-equiv="refresh" content="<?php echo $refresh ?>"></head><body marginheight=0 marginwidth=0>
<table width="100%" height="100%" cellpadding=0 cellspacing=0>
  <tr align="center">
      <td colspan="2" height="50" bgcolor="gray"> <font color="white" size="+3"> <?php echo $queuename ?> Queue</font></td>
  </tr>
  <tr>
  <td width="50%">
  <table width="100%" height="100%" cellpadding=0 cellspacing=0 style="border: 2px solid #000">
    <tr align="center">
    <td  halign="center" valign="center" height="25%" bgcolor="<?php echo $bgcolor ?>" style="color:<?php echo $fontcolor ?>; font-size: 70px;">
      Calls Waiting:
    </td>
    </tr>
    <tr>
      <td halign="center" valign="center" align="center" width="100%" bgcolor="<?php echo $bgcolor ?>" style="color:<?php echo $fontcolor ?>; font-size: 150px;"> 
        <?php echo $waitqueue ?>
      </td>
    </tr>  
</table>
</td>

<td width="50%">
<table width="100%" height="100%" cellpadding=0 cellspacing=0 style="border: 2px solid #000">
  <tr align="center" colspan="2">
    <td colspan="2" halign="center" valign="center" height="25%"  bgcolor="<?php echo $bgcolor2 ?>" style="color:<?php echo $fontcolor2 ?>; font-size: 70px;">
      Agent Status:
    </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor2 ?>">
      <td valign="center" align="center" style="color:<?php echo $fontcolor2 ?>; font-size: 40px;">
         Agents Logged in:
      </td>
      <td align="left" style="color:<?php echo $fontcolor2 ?>; font-size: 50px;" width=25%>
          <?php echo $nagents ?>
      </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor2 ?>">
      <td valign="center" align="center" style="color:<?php echo $fontcolor2 ?>; font-size: 40px;">
          Agents Available:
      </td>
      <td align="left" style="color:<?php echo $fontcolor2 ?>; font-size: 50px;" width=25%>
        <?php echo $ragents ?>
        <?php echo $snd ?>
      </td>
      
  </tr>
</table>
</td>
</tr>
<!-- Agent Status Table -->
<td colspan=2 bgcolor="gray">
<table  width="100%" height="100%" cellpadding=0 cellspacing=0 style="border: 2px solid #000">
  <tr style="color:white; font-size: 25px;">
    <td>&nbsp;</td><td>Agent</td><td>Status</td><td>Last Call</td><td>Current Caller</td>
  </tr>
  <?php echo $agentStatus ?>
</table>

</td>
</table>
</body>


</html>