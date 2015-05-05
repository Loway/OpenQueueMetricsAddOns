<?php
/* PHP QueueuMetrics PBX interactions through JSON RPC call examples --------- */
/* (c) 2015 - Marco Signorini - Loway CH - http://www.loway.ch                 */
/* For further information, please consult the QueueMetrics RPC manual         */
/* --------------------------------------------------------------------------- */


/* Configuration (please change to follow your needs) ------------------------ */
$qm_server = "127.0.0.1"; // the QueueMetrics server address
$qm_port = "8084"; // the port QueueMetrics is running on
$qm_webapp = "queuemetrics"; // the webapp name for QueueMetrics
$username = "robot";
$password = "robot";

/* Internal functions -------------------------------------------------------- */
function createOptions($inputHash) {
	$result = array();
	foreach ($inputHash as $key => $value) {
		array_push($result, $key);
		array_push($result, $value);
	}
	
	return $result;
}

function sendPOST($host, $port, $webapp, $username, $password, $action, $data) {

	$url = "http://".$host.":".$port."/".$webapp."/".$action;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(	'Content-Type: application/json',
							'Content-Length: ' . strlen($data))
						);
	$output = curl_exec($ch);
	curl_close($ch);
	
	return $output;
}

function login($extension, $agent, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;
	
	$req = array( 	'action'=> 'login',
			'extension' => $extension,
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function logout($agent, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;
	
	$req = array( 	'action'=> 'logout',
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function joinMember($extension, $agent, $queues, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;
	
	$req = array( 	'action'=> 'join',
			'extension' => $extension,
			'queues' => $queues,
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function removeMember($extension, $agent, $queues, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'remove',
			'extension' => $extension,
			'queues' => $queues,
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function pauseMember($extension, $agent, $pauseCode, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'pause',
			'extension' => $extension,
			'pause' => $pauseCode,
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function unPauseMember($extension, $agent, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'unpause',
			'extension' => $extension,
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function setCallOutcome($callId, $outcomeCode, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'calloutcome',
			'callid' => $callId,	
			'outcome' => $outcomeCode,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function customDial($extension, $targetExtension, $queue, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'customdial',
			'extension' => $extension,	
			'targetext' => $targetExtension,
			'queues' => array($queue),			
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function sendText($extension, $targetExtension, $message, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'sendtext',
			'extension' => $extension,	
			'targetext' => $targetExtension,
			'message' => $message,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function softHangup($extension, $callId, $agent, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'softhangup',
			'extension' => $extension,	
			'callid' => $callId,
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function transfer($extension, $targetExtension, $callId, $agent, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'transfer',
			'extension' => $extension,	
			'targetext' => $targetExtension,	
			'callid' => $callId,
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function inboundMonitor($techAndDeviceToMonitor, $targetExtension, $agent, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'inboundmonitor',
			'extension' => $techAndDeviceToMonitor,	
			'targetext' => $targetExtension,	
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}

function outboundMonitor($techAndDeviceToMonitor, $targetExtension, $agent, $server, $optvalues) {
	GLOBAL $qm_server, $qm_port, $qm_webapp, $username, $password;

	$req = array( 	'action'=> 'outboundmonitor',
			'extension' => $techAndDeviceToMonitor,	
			'targetext' => $targetExtension,	
			'agent' => $agent,
			'server' => $server,
			'optValues' => createOptions($optvalues)
		);
		
	$data = json_encode($req);
	
	return sendPOST($qm_server, $qm_port, $qm_webapp, $username, $password, "qm_jsonsvc_do_pbxactions.do", $data);
}


/* Some call function examples ------------------------------------------------------------------------------------- */

/* AgentCallbackLogin/Logout (valid for old asterisk where only static members are defined in a queue) */
/* Login agent/101 at extension 204, on default asterisk server, with optional channel variables set */
print login("204", "agent/101", '', array("MYVAR1"=>"VAL1", "MYVAR2"=>"VAR2"));

/* Logout agent/101 on default asterisk server; no optional channel variables set */
print logout("agent/101", '', array());

/* Join and remove members from a set of queues */
/* Join agent/101 on trix1 asterisk server queues 300 and 400 to extension 204 and no optional channel variable set */
print joinMember("204", "agent/101", array('300','400'), 'trix1', array());
/* Remove member agent/101 at extension 204 from trix1 asterisk server queues 300 and 400 */
print removeMember("204", "agent/101", array('300','400'), 'trix1', array());
	
/* Pause/unpause agents */
/* Pause agent/101 at extension 204 with pause code "10" */
print pauseMember("204", "agent/101", "10", '', array());
print unPauseMember("204", "agent/101", '', array());
	
/* Set outcome code (sale) for the specified asterisk call ID */
print setCallOutcome("1430397675.506", "sale", '', array());
	
/* Start an outbound call to 9201 and connect to the extension 204 through the queue outbound 351 */
print customDial("204", "9201", "351", '', array());

/* Send a message text to the specified extension (asterisk 10+) */
print sendText("200", "204", "This is a message from PHP", '', array());

/* Try to hangup a live call */
print softHangup("204", "1430400490.549", "agent/101", '', array());

/* Transfer a live call */
print transfer("204", "201", "1430400788.557", "agent/101", '', array());

/* Chan spy for inbound and outbound calls */
print inboundMonitor("SIP/204", "201", "agent/101", '', array());
print outboundMonitor("SIP/204", "200", "agent/101", '', array());
?>
