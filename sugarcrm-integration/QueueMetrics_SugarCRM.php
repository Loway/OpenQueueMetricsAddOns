<?php 
    // =============================================================================
    // QueueMetrics - SugarCRM integration
    // This server side script could be used to integrate QueueMetrics with SugarCRM
    // Further documentation at http://www.queuemetrics.com/manual_list.jsp
    // on the Advanced Configuration Manual
    //
    // -----------------------------------------------------------------------------
    // This file is a part of Open QueueMetrics AddOns 
    //   see https://github.com/Loway/OpenQueueMetricsAddOns for the 
    //       latest version
    //
    // Project  : SugarCRM Integration
    // Author(s): Marco Signorini
    // Licence  : LGPL or Public Domain
    // =============================================================================

    // Customize the variables below
    $server_url = "http://10.10.1.1";	// This is your SugarCRM server
    $username = "queuemetrics";		// The username for accessing data on SugarCRM
    $password = "secret";			// The password
    // End of customization

    error_reporting(0);
    require_once("nusoap/lib/nusoap.php");

    $phone_num = htmlspecialchars($_GET["callid"]);
    $agent = htmlspecialchars($_GET["agentcode"]);
    if ($phone_num == '') {
        return;
    }

    $autologon = TRUE;
    if ($agent != '') {
        
        // Insert here your code for agent SugarCRM username and password retrieval
        // The default behavior is to use a single account for all agents
        // Using default behavior requires agent authentication on SugarCRM pages

        // $username = Sugar CRM username for this agent
        // $password = Sugar CRM password for this agent
        // $autologon = TRUE;
    }

    $server_soap_url = "$server_url/soap.php?wsdl";
    $credentials = array(    
        'user_name' => $username,
        'password' => md5($password)
    );
   
    $sugar_client = new soapclient($server_soap_url, TRUE);    
    $proxy = $sugar_client->getProxy();   
    if (!$proxy) {  
            return;
    }
    
    $result = $proxy->login($credentials, 'QueueMetrics');   
    $session_id = $result['id'];
   
    $get_entry_list_parameters = array(
    
            //session id
            'session' => $session_id,
        
            //The name of the module from which to retrieve records
            'module_name' => 'Contacts',
        
            //The SQL WHERE clause without the word "where".
            'query' => "contacts.phone_work='$phone_num' OR contacts.phone_home='$phone_num' OR contacts.phone_mobile='$phone_num'",

            //The SQL ORDER BY clause without the phrase "order by".
            'order_by' => "",

            //The record offset from which to start.
            'offset' => '0',
        
            //Optional. A list of fields to include in the results.
            'select_fields' => array(),

            /*
                A list of link names and the fields to be returned for each link name.
                Example: 'link_name_to_fields_array' => array(array('name' => 'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
            */
            'link_name_to_fields_array' => array(),
        
            //The maximum number of results to return.
            'max_results' => '10',

            //To exclude deleted records
            'deleted' => '0',
        
            //If only records marked as favorites should be returned.
            'Favorites' => false,   
    );    
    
    $get_entry_list_result = $sugar_client->call('get_entry_list', $get_entry_list_parameters);
    if ($get_entry_list_result['error']['number'] == 0) {
    
            $result_count=$get_entry_list_result['result_count'];
        
            if ($result_count == 0) {
        
                // No results found. Create a new contact with pre-filled number
                $gotoUrl = "$server_url/index.php?module=Contacts&action=EditView&return_module=Contacts&return_action=index&phone_work=$phone_num";
            
            } else if ($result_count == 1) {
            
                //Take the entry ID we need
                $entry_id = $get_entry_list_result['entry_list'][0]['id'];
            
                // Show the contact page detail
                $gotoUrl = "$server_url/index.php?action=DetailView&module=Contacts&record=$entry_id";
            
            } else if ($result_count > 1) {
        
                // We have more than one result. Show the entry page
                $gotoUrl = "$server_url/index.php?&searchFormTab=advanced_search&module=Contacts&action=index&query=true&phone_advanced=$phone_num";
            }

        // Logon on SugarCRM Web
        if ($autologon == TRUE) {
                $result = $proxy->seamless_login($session_id);
            $gotoUrl = $gotoUrl . "&MSID={$session_id}";
        }

        // Redirect to SugarCRM page
        header("Location: $gotoUrl");
    } 
?>
