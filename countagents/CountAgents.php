#!/usr/bin/php

<?php
    // ==========================================================================
    // CountAgents.php
    // A PHP script that counts how many unique agents are running 
    // a specified queue in the given period.
    //
    // --------------------------------------------------------------------------
    // This file is a part of Open QueueMetrics AddOns 
    //    see http://open-qm-addons.sourceforge.net/ for the latest version
    //
    // Project  : CountAgents
    // Author(s): Marco Signorini
    // Licence  : LGPL
    // ==========================================================================

    require_once 'XML/RPC.php';
    require_once 'Console/Getopt.php';
    
    // ==== Here are customizable parameters ====
    
    // Agent label on table
    $agentLabel = "Handled by";

    // List of exceptions
    $agentNameExceptions = array ( "SIP/" );
    
    // === End of customizable parameters =======

    // These are the default values
    $qm_server = "localhost"; // the QueueMetrics server address
    $qm_port = "8080"; // the port QueueMetrics is running on
    $qm_webapp = "queuemetrics"; // the webapp name for QueueMetrics
    
    // Retrieve the command line options
    $Options = new Console_Getopt ();
    $options = $Options->getopt($argv, "h:p:w:u:x:d:");
    $ParsedOptions = $options[0];
    $Arguments = $options[1];
    $qm_period = false;
    
    // Parse the command line options
    $qm_username = "";
    $qm_password = ""; 
    for ($N = 0; $N < sizeof($ParsedOptions); $N++)
    {
        if ($ParsedOptions[$N][0] == 'h')
            $qm_server = $ParsedOptions[$N][1];
            
        if ($ParsedOptions[$N][0] == 'p')
            $qm_port = $ParsedOptions[$N][1];
            
        if ($ParsedOptions[$N][0] == 'w')
            $qm_webapp = $ParsedOptions[$N][1];
            
        if ($ParsedOptions[$N][0] == 'u')
            $qm_username = $ParsedOptions[$N][1];
            
        if ($ParsedOptions[$N][0] == 'x')
            $qm_password = $ParsedOptions[$N][1];
        
        if ($ParsedOptions[$N][0] == 'd')
        {
            $qm_period = true;
            $qm_days = $ParsedOptions[$N][1];
        }
    }
    
    // Print user's manual if requested
    if ($argc < 2 || in_array($argv[1], array('--help', '-help', '-h', '-?')) || 
        ((sizeof($Arguments) != 3) && ($qm_period == false)) ||
        ((sizeof($Arguments) < 1) && ($qm_period == true)) || 
        (strlen($qm_username) == 0) || (strlen($qm_password) == 0))
    {
        printUsage();
        exit();
    }    

    // Retrieve the queue name
    $qm_queuename = $Arguments[0];

    // Retrieve the Start and End date period
    $StartDate = 0;
    $EndDate = 0;
    if ($qm_period == true)
    {
        $now = time();
        $EndDate = date( "Ymd" , $now );
        $start = $now - ($qm_days * 24 * 60 * 60);
        $StartDate = date ( "Ymd" , $start );
    }
    else
    {
        $StartDate = $Arguments[1];
        $EndDate = $Arguments[2];
    }

    // Format the date in the appropriate way
    $StartDate = formatDate($StartDate);
    $EndDate = formatDate($EndDate);

    // Check for parameter consistency
    if ($qm_queuename == "")
        exit ("The queuename is not specified. Please check the inserted parameters\n");
        
    if (($StartDate == "") || ($EndDate == ""))
        exit ("Error on date, please check inserted parameters\n");
    
    // set which response blocks we are looking for
    $req_blocks = new XML_RPC_Value(array(new XML_RPC_Value("DetailsDO.CallsOK"),new XML_RPC_Value("DetailsDO.CallsKO")), "array");
    
    // General invocation parameters
    $params = array(
        new XML_RPC_Value("$qm_queuename"),
        new XML_RPC_Value("$qm_username"),
        new XML_RPC_Value("$qm_password"),
        new XML_RPC_Value(""), 
        new XML_RPC_Value(""),
        new XML_RPC_Value("$StartDate.0:00:00"),
        new XML_RPC_Value("$EndDate.23:59:59"),
        new XML_RPC_Value(""),
        $req_blocks
    );
    
    $msg = new XML_RPC_Message('QM.stats', $params);
    $cli = new XML_RPC_Client("/$qm_webapp/xmlrpc.do", $qm_server, $qm_port);
    
    $resp = $cli->send($msg);
    if (!$resp) 
    {
        echo 'Communication error: ' . $cli->errstr;
        exit;
    }
    
    if ($resp->faultCode()) 
    {
        echo 'Fault Code: ' . $resp->faultCode() . "\n";
        echo 'Fault Reason: ' . $resp->faultString() . "\n";
    } 
    else 
    {
        $val = $resp->value();
        $blocks = XML_RPC_decode($val);

        $uniqueAgent = array();
        $uniqueAgent = countUniqueAgent( "DetailsDO.CallsOK", $blocks, $agentNameExceptions, $agentLabel, $uniqueAgent );
        $uniqueAgent = countUniqueAgent( "DetailsDO.CallsKO", $blocks, $agentNameExceptions, $agentLabel, $uniqueAgent );
        
        print "Unique agents on period $StartDate to $EndDate: \n" . sizeof($uniqueAgent) . "\n";
    }
    
    // ===== Internal function implementations ===============================================================
    
    function formatDate( $InputDate )
    {
        if (strlen($InputDate) != 8)
            return "";
            
        $Year = substr($InputDate, 0, 4);
        $Month = substr($InputDate, 4, 2);
        $Day = substr($InputDate, 6, 2);
        
        return "$Year-$Month-$Day";
    }
    
    function countUniqueAgent( $blockname, $blocks, $agentNameExceptions, $agentLabel, $uniqueAgentList )
    {
        // Retrieve the required block    
        if (!isset($blocks[$blockname]))
        {
            print ( "Warning: the table $blockname has not been found. Please check the parameters and/or the username properties.\n");
            return $uniqueAgentList;
        }

        $block = $blocks[$blockname];
        
        // That's an invalid block
        if (sizeof($block) < 1)
            return $uniqueAgentList;

        // Search the "Handled by" column
        $TargetCol = -1;
        for ($n = 0; ($n < sizeof($block[0])) && ($TargetCol == -1); $n++)
        {
            if ($block[0][$n] == $agentLabel)
                $TargetCol = $n;
        }
        
        // No valid column found on this block
        if ($TargetCol == -1)
            return $uniqueAgentList;
            
        // Count how many unique agents are present in the block
        for ($row = 1; $row < sizeof($block); $row++)
        {
            if (isAValidAgentName($block[$row][$TargetCol], $agentNameExceptions))
            {
                if (isset($uniqueAgentList[$block[$row][$TargetCol]]))
                    $uniqueAgentList[$block[$row][$TargetCol]]++;
                else
                    $uniqueAgentList[$block[$row][$TargetCol]] = 1;
            }
        }
        
        return $uniqueAgentList;
    }
    
    // Check if the user agent is valid or not following the specified exception rules
    function isAValidAgentName( $agentName, $agentNameExceptions )
    {
        $agentValid = true;
        for ($n = 0; ($n < sizeof($agentNameExceptions)) && ($agentValid == true); $n++)
        {
            $agentNameException = $agentNameExceptions[$n];
            if ( strncmp( $agentName, $agentNameException, strlen($agentNameException) ) == 0 )
                $agentValid = false;
        }

        return $agentValid;
    }
    
    // Print a short user's manual
    function printUsage ()
    {
        print ( "Loway Research - home of Queuemetrics (c) 2008\n\n" );
        print ( "Counts how many unique agents are running in a specified period.\n");
        print ( "Use the syntax below:\n\n" );
        print ( "CountAgents.php [-h host] [-p port] [-w application] [-d days] -u username -x password queuename [yyyymmdd] [YYYYMMDD] \n\n" );
        print ( "Where:\n" );
        print ( "\thost: hostname or IP address (default: localhost)\n" );
        print ( "\tport: port where the application runs (default: 8080)\n" );
        print ( "\tapplication: the web application name (default: queuemetrics)\n" );
        print ( "\tdays: the number of last days where the query will be run\n" );
        print ( "\tusername: the username to be used to log in\n" );
        print ( "\tpassword: the password to be used to log in\n" );
        print (" \tqueuename: the pivot queue name to look at\n" );
        print ( "\tyyyymmdd: is the start date of period where the query will be run\n" );
        print ( "\tYYYYMMDD: is the end date of period where the query will be run\n" );
        print ( "\nThe last two parameters are optional only if the -d option is specified\n");
    }
    
    // Printout the block (for debug purposes)
    function printBlock( $blockname, $blocks ) 
    {
        echo "Response block: $blockname\n";
        $block = $blocks[$blockname];
        for ( $r = 0; $r < sizeof( $block ); $r++ ) 
        {
            for ( $c = 0; $c < sizeof( $block[$r] ); $c++ ) 
            {
                echo( "\t" . $block[$r][$c] );
            }
            
            echo( "\n" );
        }
    }
    
    // ==========================================================================
    // Change history:
    // --------------------------------------------------------------------------
    // 
    // ==========================================================================
?>
