<?php
	// =========================================================================
	// QueueMetrics - SugarCRM integration
	// This server side script could be used to integrate QueueMetrics with
	// SugarCRM.
	// -------------------------------------------------------------------------
	//
	// Project	: Integrate QueueMetrics and SugarCRM
	// Author	: W.I.S.E, Care Team
	//			  ** Project inspired by the script of Marco Signorini **
	// Licence	: LGPL or Public Domain
	//
	// -------------------------------------------------------------------------
	// This is an entry point script.
	// =========================================================================

	/*
		Customize the following variables:
	*/
	// Your SugarCRM server.
	$url = "http://your.sugarcrm.server";
	// The username for accessing SugarCRM data.
	$username = "your_sugar_username";
	// The password.
	$password = "your_sugar_password";
	/* End of customization */

	/*
		Here we are reading input variables passed by QM.
	*/
	// Get phone number of caller that is passed by QM.
	$origCallerid = htmlspecialchars($_GET["callerid"]);
	// Get code of agent that is passed by QM.
	$agent = htmlspecialchars($_GET["agentcode"]);
	
	// If no caller id passed, then we redirect to SugarCRM base URL.
	if (empty($origCallerid)) { 
		header("Location: $finalDestination");
	}
		// We then remove leading zeros if any. It is necessary to fine tune search
		// results since phone numbers in SugarCRM might be stored in international
		// or local format with or without leading zeros.
	$callerid = ltrim($origCallerid, '0');

	// If no agent code passed, well, this is left to you to decide.
	if (empty($agent)) {
		// Do some work...
	}
	/* End of reading input variables. */

	// Single Agent Mode:
	// If you wish to use a single SugarCRM user account for all agents, then
	// you may change this value to default to "true".
	// Here, we assume that each agent has a corresponding SugarCRM user.
	$autologon = false;

	// Initial SugarCRM page URL to redirect to.
	$finalDestination = $url;

	/*
		Connect to SugarCRM and open a session.
	*/
	// Include our custom class to make REST calls.
	require_once("wSuiteRest.php");
	// Initialize wSuiteRest class to open a session with SugaCRM.
	$wRest = new wSuiteRest($url, $username, $password);
	// In case of error, we print it on screen and get out of here.
	if (!empty($wRest->error['number'])) {
		echo "<pre>";
		print('<b>Error#' . $wRest->error['number'] . ' ' . $wRest->error['name'] . '<br>Description: </b>' . $wRest->error['description']);
		echo "</pre>";
		return;
	}

	/*
		Get a list of possible records and prepare the 
	*/
	// Prepare parameters.
	$get_entry_list_parameters = array(
		// The session id.
		'session' => $wRest->session_id,
		// The name of the module from which to retrieve records.
		'module_name' => 'Contacts',
		// The SQL WHERE clause without the word "where".
		'query' => "contacts.phone_work LIKE '%$callerid' OR contacts.phone_home LIKE '%$callerid' OR contacts.phone_mobile LIKE '%$callerid' OR contacts.phone_other LIKE '%$callerid' OR contacts.phone_fax LIKE '%$callerid'",
		// The SQL ORDER BY clause without the phrase "order by".
		'order_by' => "",
		// The record offset from which to start.
		'offset' => '0',
		// A list of fields to include in the results.
		'select_fields' => array('id',),
		// A list of link names and the fields to be returned for each
		// link name.
		// Example:
		//'link_name_to_fields_array' => array(
		//	array(
		//		'name' => 'email_addresses',
		//		'value' => array(
		//			'id',
		//			'email_address',
		//			'opt_out',
		//			'primary_address'
		//		)
		//	)
		//)
		'link_name_to_fields_array' => array(),
		// The maximum number of results to return.
		'max_results' => '10',
		// Exclude deleted records?
		'deleted' => '0',
		// Whether records marked as favorites should only be returned.
		'favorites' => false,
	);

	// Retreive data from SugarCRM.
	$get_entry_list_result = $wRest->call('get_entry_list', $get_entry_list_parameters);
	// In case of error, we print it on screen and get out of here.
	if (!empty($wRest->error['number'])) {
		echo "<pre>";
		print('<b>Error#' . $wRest->error['number'] . ' ' . $wRest->error['name'] . '<br>Description: </b>' . $wRest->error['description']);
		echo "</pre>";
		return;
	}

	// Now, here what this is all about...
	$total_entries = $get_entry_list_result->total_count;
	if ($total_entries == 0) {
		// No entries found; create a new contact with pre-filled caller number.
		$finalDestination = "$url/index.php?module=Contacts&action=EditView&return_module=Contacts&return_action=index&phone_work=$origCallerid";
	}
	else if ($total_entries == 1) {
		// One match; take the entry ID.
		$entry_id = $get_entry_list_result->entry_list[0]->id;
		$finalDestination = "$url/index.php?module=Contacts&action=DetailView&record=$entry_id";
	}
	else if ($total_entries > 1) {
		// More than one match; show entries in search page.
		$finalDestination = "$url/index.php?&searchFormTab=advanced_search&module=Contacts&action=index&query=true&phone_advanced=%25$callerid";
	}

	// In case Single Agent Mode (autologon) is enabled, we verify that SugarCRM
	// session is authenticated; otherwise, logout current session.
	if ($autologon) {
		$seamless_login_parameters = array(
			'session' => $wRest->session_id,
		);

		$seamless_login_result = $wRest->call('seamless_login', $seamless_login_parameters);

		if (!empty($wRest->error['number'])) {
			echo "<pre>";
			print('<b>Error#' . $wRest->error['number'] . ' ' . $wRest->error['name'] . '<br>Description: </b>' . $wRest->error['description']);
			echo "</pre>";
			return;
		}

		if ($seamless_login_result == 1) {
			$finalDestination = $finalDestination . "&MSID={$wRest->session_id}";
		}
	}
	else {
		$wRest->endSession();
	}

	// Finally results...
	// Redirect to SugarCRM page
	header("Location: $finalDestination");

	// =========================================================================
	// Change History:
	// -------------------------------------------------------------------------
	// 
	// 2014.11.12:
	// First check-in.
	// 
	// =========================================================================
?>
