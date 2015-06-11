<?php
/*
  Author: Barry Flanagan <barry AT flantel DOT com>
  Date: 21 Nov 2007

  Copyright 2007-2011 Barry Flanagan, but feel free :-)

  Small script to grab the calls waiting and Agent information for a given queue from the QueueMetrics XML-RPC service and
  create a simple display for use on a wallboard.

  Display colours will change from Green to Yellow to Red depending on Calls waiting, Ready Agents. Will also sound an alarm.

  This has proved useful in a large callcentre where the standard QM RT monitoring screen is too detailed and hard to see
  from a distance.

  To use, simply define the queues you want to monitor as $queueids in the switch statement, give the queuegroups a name
  and set the $ROBOT_USER user and $PASSWORD, as well as the IP address of your QM server

  Current wallboard is at http://202.91.161.156:8080/queuemetrics/qm/wallboard_classic.jsp
  New URL is     http://202.91.161.156/wallboard.php?queue=<queuegroup> - you can add other parameters;

  See the _GETs below  and set the $ROBOT_USER user and $PASSWORD, as well as the IP address of your QM server

 This script relies on Outbound queues to be names in QM as *-Outbound, so that we can optionally display or not display the actual outbound queue.
 QM Sub-queues are supported, and rely on a naming convention in QM such as "Master queue.sub queue" -
 i.e. the name for the queue is made up of the Mater queue name, followed by a period followed by the sub queue name. If this convention is
 followed, then you will get a single wallboard column for the Maste Queue, rather than a column for each and every subqueue.
*/

require_once 'XML/RPC.php';
$ROBOT_USER="robot";
$PASSWORD="robot";

$qm_server = "10.10.5.37"; // the QueueMetrics server address
$qm_port   = "8080";        // the port QueueMetrics is running on
$qm_webapp = "queuemetrics"; // the webapp name for QueueMetrics


isset($_GET['refresh'])?$refresh = $_GET['refresh']:$refresh=5; // how often to refresh the page
isset($_GET['queue'])?$queuegroup = $_GET['queue']:$queuegroup='inbound'; // Which queue group to display (see switch statement below)
isset($_GET['showstatus'])?$showstatus = $_GET['showstatus']:$showstatus=1; // Whether to show the Agent Status for a queue group
isset($_GET['showcurrcalls'])?$showcurrcalls = $_GET['showcurrcalls']:$showcurrcalls=1; // Whether to show the Current Calls for a queue group
isset($_GET['showout'])?$showoutbound = $_GET['showout']:$showoutbound=1; //Whether to show the Outbound queue as a separate column for a queue group
$useSubqueues = true;
switch ($queuegroup) {
  case 'all':
    $queueids = "q300|q301";
    $queuename='Inbound';
  break;;

  case 'outbound':
    $queueids = "q300|q301";
    $outboundqueue = 'Outbound';
    $queuename='Outbound';
    $showstatus=1;
    $showcurrcalls=1;
    $showoutbound=1;
  break;;

case 'inbound':
    $queueids = "q300|q301";
    $showoutbound = 0;
    $outboundqueue = 'TechSupp-Outbound';
    $queuename='Technical Support';
  break;;
}
?>
<html><head><meta http-equiv="refresh" content="<?php echo $refresh ?>"><title><?php echo $queuename . " Wallboard" ?></title></head><body marginheight=0 marginwidth=0>
<table width="100%" cellpadding=0 cellspacing=0 border="0">
  <tr>
<?php
$queuelist = array_merge(explode("|", $queueids),explode("|",$outboundqueue));
$req_blocks_rt = new XML_RPC_Value(array(
      new XML_RPC_Value("RealtimeDO.RTCallsBeingProc"),
      new  XML_RPC_Value("RealtimeDO.RTAgentsLoggedIn"),
      new XML_RPC_Value("RealtimeDO.RTRiassunto")
           ), "array");
// First we need to get the number of ready agents for the common queues. This is a bug in QM as it will report
// an agent as free in one queue even if they are on a call in another.
$params_rt = array(
     new XML_RPC_Value($queueids),
           new XML_RPC_Value($ROBOT_USER),
           new XML_RPC_Value($PASSWORD),
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
    $agent = getAgentLoggedIn( "RealtimeDO.RTAgentsLoggedIn", $blocks_rt );
    $agentStatus = getCurrentCalls( "RealtimeDO.RTCallsBeingProc", $blocks_rt);
    $print = 1;
    getQueueStatus( "RealtimeDO.RTRiassunto", $blocks_rt, $queueids, $print);
    printQueues($queue);

}
    isset($queue['all selected']['ragents'])?$ragents = $queue['all selected']['ragents']:$ragents = 0;
    // Get the waiting calls and number of agents for these queues

// Get the current queue stats
function getQueueStatus( $blockname, $blocks, $queueids, $print ) {
    global $queue, $queuelist, $ragents, $useSubqueues;
     $block = $blocks[$blockname];
     for ( $r = 1; $r < sizeof( $block ) ; $r++ ) {
       $currentQueue = $block[$r][1];
       if ( $currentQueue == "all selected") {
         $ragents = $block[$r][3];
         continue;
        }
       if ((1 == substr_count($currentQueue,'.')) && ($useSubqueues)) {
         list($masterqueue, $subqueue) = explode('.', $currentQueue);

         $currentQueue = $masterqueue;
         isset($queue[$currentQueue]['waiting'])?$queue[$currentQueue]['waiting'] = $queue[$currentQueue]['waiting']:$queue[$currentQueue]['waiting'] = 0;
         isset($queue[$currentQueue]['incalls'])?$queue[$currentQueue]['incalls'] = $queue[$currentQueue]['incalls']:$queue[$currentQueue]['incalls'] = 0;
         isset($queue[$currentQueue]['outcalls'])?$queue[$currentQueue]['outcalls'] = $queue[$currentQueue]['outcalls']:$queue[$currentQueue]['outcalls'] = 0;
         $queue[$currentQueue]['name'] =  $masterqueue;
         $queue[$currentQueue]['waiting'] =  $block[$r][7] + $queue[$currentQueue]['waiting'];
         $queue[$currentQueue]['nagents'] = $block[$r][2];
         $queue[$currentQueue]['ragents'] = $block[$r][3];
         $queue[$currentQueue]['incalls'] = $block[$r][8] + $queue[$currentQueue]['incalls'];
         $queue[$currentQueue]['outcalls'] = $block[$r][9] + $queue[$currentQueue]['outcalls'];


       } else {
         $queue[$currentQueue]['waiting'] =  $block[$r][7];
         $queue[$currentQueue]['name'] =  $block[$r][1];
         $queue[$currentQueue]['nagents'] = $block[$r][2];
         $queue[$currentQueue]['ragents'] = $block[$r][3];
         $queue[$currentQueue]['incalls'] = $block[$r][8];
         $queue[$currentQueue]['outcalls'] = $block[$r][9];
//         if (!$useSubqueues) $queue[$currentQueue]['name'] = str_replace("."," ",$queue[$currentQueue]['name']);
       }
//      $waitqueue = $queue[$currentQueue]['waiting'];
//      $nagents = $queue[$currentQueue]['nagents'];
//      $currcalls = $queue[$currentQueue]['incalls'] + $queue[$currentQueue]['outcalls'];
      if (!isset($queue[$currentQueue]['maxholdtime'])) $queue[$currentQueue]['maxholdtime'] = '0:00';
      if($queue[$currentQueue]['maxholdtime'] == '') $queue[$currentQueue]['maxholdtime'] = '0:00';

      if (preg_match('/utbound/', $currentQueue)) {
        $ragents = $queue[$currentQueue]['ragents'];
        $queue[$currentQueue]['waiting'] = '0';
        $queue[$currentQueue]['maxholdtime'] = '0:00';
      }
    }
function orderBy(&$data, $field) {
  $code = "return strnatcmp(\$a['$field'], \$b['$field']);";
  usort($data, create_function('$a,$b', $code));
}

function printQueues($queues) {
  orderBy($queues,'name');
  global $ragents, $showcurrcalls, $showstatus, $showoutbound;
  $tdwidth = round(100/sizeof($queues),1) . "%";
  foreach ( $queues as $queue  ) {
    // width for the tables
    $currentQueue = $queue['name'];
    if (1 == substr_count($currentQueue,'Master')) continue;
    if (( $showoutbound == 0) && (1 == substr_count($currentQueue,'utbound'))) continue;
    $queueholdtime = $queue['maxholdtime'];
    $waitqueue = $queue['waiting'];
    $nagents = $queue['nagents'];
    $currcalls = $queue['incalls'] + $queue['outcalls'];
    $ragents = $queue['ragents'];

    // Set up colour and formatting  depending on the status of the queue
    if( $waitqueue == 0 ) {
      $waitbgcolor = "green";
      $snd = null;
      $waitfontcolor = "white";
    } elseif ($waitqueue == 1){
      $waitbgcolor = "yellow";
      $snd = '<EMBED SRC="/sounds/dingdong.wav" HIDDEN="true" AUTOSTART="true">';
      $waitfontcolor = "black";
    } elseif ($waitqueue > 1) {
      $waitbgcolor = "red";
      $snd = '<EMBED SRC="/sounds/ringer.wav" HIDDEN="true" AUTOSTART="true">';
      $waitfontcolor = "white";
      $waitqueue = "<blink>" . $waitqueue . "</blink>";
    }
    if( $ragents > 2 ) {
      $ragentsbgcolor = "green";
      $ragentsfontcolor = "white";
    } elseif ($ragents == 2){
      $ragentsbgcolor = "yellow";
      $ragentsfontcolor = "black";
    } elseif ($ragents <= 1) {
      $ragentsbgcolor = "red";
      $ragentsfontcolor = "white";
    }
    $currentQueue = str_replace("."," ",$currentQueue);
    print <<<END
    <td width="$tdwidth" valign="top">
  <div style="border: 2px solid #FFF; width: 100%">
        <div width="100%" align="center" valign="center" style="color:white; font-size: 45px; background-color: black; height: 49 px;">$currentQueue</div>
        <div style="background-color: grey; height:3px">&nbsp;</div>
        <div align="center" style="color:$waitfontcolor; font-size: 25px; background-color: $waitbgcolor; ">Calls Waiting</div>
        <div align="center" style="color:$waitfontcolor; font-size: 85px; background-color: $waitbgcolor; ">$waitqueue</div>
        <div align="center" style="color:$waitfontcolor; font-size: 25px; background-color: $waitbgcolor; ">Max $queueholdtime</div>
        <div style="background-color: grey; height:3px">&nbsp;</div>
END;
if ($showcurrcalls == 1) {
  print <<<END
        <div align="center" style="color:white; font-size: 25px; background-color: green">Curr Calls:</div>
        <div align="center" style="color:white; font-size: 85px;  background-color: green">$currcalls</div>
END;
}
print <<<END
        <div style="background-color: grey; height:3px">&nbsp;</div>
        <div align="center" style="color:$ragentsfontcolor; font-size: 25px; background-color: $ragentsbgcolor">Rdy Agnt: <br></div>
        <div align="center" style="color:$ragentsfontcolor; font-size: 40px; background-color: $ragentsbgcolor">$ragents/$nagents </div>
     </div>
    </td>

END;
    }
  }
}
// Get the actual status of each agent
function getAgentLoggedIn( $blockname, $blocks ) {
//    global $agent;
    $agent = array();
    $block = $blocks[$blockname];
     for ( $r = 1; $r < sizeof( $block ) ; $r++ ) {
       $agent[$block[$r][2]]['name'] = $block[$r][2];
       $agent[$block[$r][2]]['lastlogin'] = $block[$r][3];
       $agent[$block[$r][2]]['queues'] = $block[$r][4];
       $agent[$block[$r][2]]['extension'] = $block[$r][5];
       $agent[$block[$r][2]]['onpause'] = $block[$r][6];
       $agent[$block[$r][2]]['srv'] = $block[$r][7];
       $agent[$block[$r][2]]['lastcall'] = $block[$r][8];
       $agent[$block[$r][2]]['onqueue'] = $block[$r][9];
       $agent[$block[$r][2]]['caller'] = 'none';
       $agent[$block[$r][2]]['entered'] = null;
       $agent[$block[$r][2]]['waiting'] = null;
       $agent[$block[$r][2]]['duration'] = null;
      }
    return $agent;
}
// Get the current call details for each agent
function getCurrentCalls( $blockname, $blocks) {
    global $agent, $queue, $useSubqueues;
    $block = $blocks[$blockname];
     for ( $r = 1; $r < sizeof( $block ) ; $r++ ) {
       $agentname = $block[$r][6];
       $agent[$agentname]['queue'] = $block[$r][1];
       $agent[$agentname]['caller'] = $block[$r][2];
       $agent[$agentname]['entered'] = $block[$r][3];
       $agent[$agentname]['waiting'] = $block[$r][4];
       $agent[$agentname]['duration'] = $block[$r][5];
        if ($agent[$agentname]['duration'] == '-') {
          if (1 == substr_count($agent[$agentname]['queue'],'.') && ($useSubqueues)) {
            list($agent[$agentname]['queue'], $subqueue) = explode('.', $agent[$agentname]['queue']);
          }
            if (toSec($agent[$agentname]['waiting']) > toSec($queue[$agent[$agentname]['queue']]['maxholdtime'])) $queue[$agent[$agentname]['queue']]['maxholdtime'] = $agent[$agentname]['waiting'] ;
        }
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
	//$lastcalltime = 0;

        // get the last call time for the agent, and convert to epoc time
        if ((isset($agent[$r]['lastcall'])) && (strstr($agent[$r]['lastcall'],':'))) {
          list($h, $m, $s) = explode(':', $agent[$r]['lastcall']);
          $lastcalltime = mktime($h,$m,$s);
        }

        if ($agent[$r]['duration'] != '') {
          $status = "On Call since " . $agent[$r]['entered'] . " (" . $agent[$r]['duration'] . " mins)";

  $bgcolor3 = $rowcolor;
          $fontcolor3 = "red";
        } elseif (!preg_match('/-/',$agent[$r]['onpause'])){
          $status = "On Pause since " . $agent[$r]['onpause'];
          $bgcolor3 = $rowcolor;
          $fontcolor3 = "blue";
        } elseif (time() - $lastcalltime  < 5) {
          $status = "Wrapping up";
          $bgcolor3 = $rowcolor;
          $fontcolor3 = "yellow";
        } else {
          $status = "Available";
          $bgcolor3 = $rowcolor;
          $fontcolor3 = "black";
        }
        if (isset($agent[$r]['name'])) {
          if(!isset($agent[$r]['queue'])) $agent[$r]['queue'] = '';
          $agentStatus .="<tr bgcolor=\"" . $rowcolor . "\" style=\"color:black; font-size: 20px;\"><td>&nbsp;</td><td>" . $agent[$r]['name'] . "</td><td style=\"color:" . $fontcolor3 . ";\" bgcolor=\"" . $bgcolor3 . "\" >" . $status .  "</td><td>" . $agent[$r]['lastcall'] .  "</td><td>" . $agent[$r]['queue'] . "</td></tr>";
        }
      }
    return $agentStatus;
}
function toSec ($hms) {
      list($h, $m, $s) = explode (":", $hms);
      $seconds = 0;
      $seconds += (intval($h) * 3600);
      $seconds += (intval($m) * 60);
      $seconds += (intval($s));
      return $seconds;
}
?>

</tr><tr><td>&nbsp;</td></tr><tr>
<?php
if ($showstatus == 1) {
print <<<END
<!-- Agent Status Table -->
<td colspan=20 bgcolor="gray" valign="top">
<table  width="100%" height="100%" cellpadding=0 cellspacing=0 style="border: 2px solid #000">
  <tr style="color:white; font-size: 25px;">
    <td>&nbsp;</td><td>Specialist</td><td>Status</td><td>Last Call</td><td>Current Queue</td>
  </tr>
  $agentStatus
</table>
</td>
END;
}


?>
</table>
</body>
</html>

