<?php   
    // =============================================================================
    // QueueMetrics - VTigerCRM integration
    // This server side script could be used to integrate QueueMetrics with VTiger
    // Further documentation at http://www.queuemetrics.com/manual_list.jsp
    // on the Advanced Configuration Manual
    //
    // -----------------------------------------------------------------------------
    // This file is a part of Open QueueMetrics AddOns 
    //   see https://github.com/Loway/OpenQueueMetricsAddOns for the 
    //       latest version
    //
    // Project  : VTiger Integration
    // Author(s): Marco Signorini
    // Licence  : LGPL
    // =============================================================================
    
    // In order to use this you need to install
    // - HTTP_Client which can be installed by doing a pear install HTTP_Client
    // - Zend Json

    require_once "HTTP/Client.php";
    require_once "Zend/Json.php";

    // Customize the variables below
    $server_url = "http://10.10.0.1";	// This is your SugarCRM server
    $username = "queuemetrics";		    // The username for accessing data on SugarCRM
    $accessKey = "5qsoQZaiXyn2lMPF";    // The access key found on the user's preference panel
    // End of customization

    $phone_num = htmlspecialchars($_GET["callid"]);
    
    RedirectTo();
    
    
    
    function RedirectTo() {
    
        global $server_url, $username, $accessKey, $phone_num;
  
        $server_api = $server_url . "/webservice.php";
     
        // Get a challenge token
        $httpc = new HTTP_Client();
        $httpc->get("$server_api?operation=getchallenge&username=$username");
        $response = $httpc->currentResponse();
        $jsonResponse = Zend_JSON::decode($response['body']);
        if($jsonResponse['success']==false) {
            die('QueueMetrics-VTiger integration error:'.$jsonResponse['error']['errorMsg']);
        }
        
        $challengeToken = $jsonResponse['result']['token'];
        
        // Login to the VTigerCRM platform
        $generatedKey = md5($challengeToken.$accessKey);
        $httpc->post($server_api, array('operation'=>'login','username'=>$username,'accessKey'=>$generatedKey), true);
        $response = $httpc->currentResponse();
        $jsonResponse = Zend_JSON::decode($response['body']);
        if($jsonResponse['success']==false) {
            die('QueueMetrics-VTiger integration error:'.$jsonResponse['error']['errorMsg']);
        }

        $sessionId = $jsonResponse['result']['sessionName'];
        $userId = $jsonResponse['result']['userId'];

        // Search for the contact with the received phone number
        $query = "SELECT * FROM Contacts WHERE phone='$phone_num' OR homephone='$phone_num' OR otherphone='$phone_num';";
        $queryParam = urlencode($query);
        $params = "sessionName=$sessionId&operation=query&query=$queryParam";
        $httpc->get("$server_api?$params");
        $response = $httpc->currentResponse();
        $jsonResponse = Zend_JSON::decode($response['body']);
        if($jsonResponse['success']==false) {
            logout($sessionId);
            die('QueueMetrics-VTiger integration error:'.$jsonResponse['error']['errorMsg']);
        }

        $params = "module=Contacts&action=EditView&return_action=DetailView&phone=".urlencode($phone_num);
        $gotoUrl = "$server_url/index.php?$params";
        $contacts = $jsonResponse['result'];
        if (count($contacts) == 1){

            // Take the first item in the list
            $itemId = $contacts[0]['id'];
            $ids = explode('x',$itemId);
            $itemId = $ids[1];

            $gotoUrl = "$server_url/index.php?module=Contacts&action=DetailView&record=$itemId";
        } else if (count($contacts) > 1){

            $params = "search_field=phone&searchtype=BasicSearch&search_text=".urlencode($phone_num)."&query=true&file=index&module=Contacts&search=true&action=ListView";
            $gotoUrl = "$server_url/index.php?$params";
        }

        logout($sessionId);

        header("Location: $gotoUrl");
    }

    function logout($sessionId) {
    
        global $server_url;

        $params = "operation=logout&sessionName=$sessionId";
        $httpc = new HTTP_Client();
        $httpc->get("$server_url?$params");
    }
?>
