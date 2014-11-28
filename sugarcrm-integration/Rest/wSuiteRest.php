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
	// wSuiteRest class
	// Designed to handle communications with SugarCRM over REST
	// =========================================================================

	class wSuiteRest {
		var $rest_url;
		var $session_id;
		var $error;

		/*
			Constructor.
		*/
		function wSuiteRest($url, $username, $password) {
			$this->error = array('number'=>'0', 'name'=>'No error', 'description'=>'No error');
			$this->rest_url = $url.'/service/v4_1/rest.php';	// Path to REST Service V4.1 (SugarCRM 6.5 and later)
			// Attempt to login
			$parameters = array(
				'user_auth' => array(
					'user_name' => $username,
					'password' => md5($password),
					'version' => '1'
				),
				'application_name' => 'wSuiteCRM',
				'name_value_list' => array(),
			);

			$result = $this->call('login', $parameters);
			// get session id
			if (empty($this->error['number'])) {
				if (!empty($result->id)) {
					$this->session_id = $result->id;
				}
			}
		}

		/*
			Logs out and ends SugarCRM session.
		*/
		function endSession() {
			if (!empty($this->session_id)) {
				$parameters = array(
					'session' => $this->session_id,
				);

				$this->call('logout', $parameters);
				$this->session_id = null;
			}
		}

		/*
			Makes cURL request.
		*/
		function call($method, $parameters) {
			$result = null;
			try {
				ob_start();
				$curl_request = curl_init();

				curl_setopt($curl_request, CURLOPT_URL, $this->rest_url);
				curl_setopt($curl_request, CURLOPT_POST, 1);
//				curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				curl_setopt($curl_request, CURLOPT_HEADER, 0);
//				curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
//				curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

				$jsonEncodedData = json_encode($parameters);
				$postArgs = array(
					'method' => $method,
					'input_type' => 'JSON',
					'response_type' => 'JSON',
					'rest_data' => $jsonEncodedData
				);

				curl_setopt($curl_request, CURLOPT_POSTFIELDS, $postArgs);
				$response = curl_exec($curl_request);
				curl_close($curl_request);
//				$response = explode("\r\n\r\n", $response, 2);
				$result = json_decode($response);
				ob_end_flush();

				// When Sugar returns nothing...
				if (!$result) {
					$this->error = array('number'=>'1', 'name'=>'Call error', 'description'=>'Invalid session, parameters or method call.');
					return;
				}

				// When Sugar returns error...
				if (!empty($result->number)) {
					$this->error = array('number'=>$result->number, 'name'=>$result->name, 'description'=>$result->description);
					return;
				}
			}
			catch (Exception $e) {
				$this->error = array('number'=>$e->getCode(), 'name'=>$e->getMessage(), 'description'=>$e->getMessage());
			}

			return $result;
		}
	}
	// =========================================================================
	// Change History:
	// -------------------------------------------------------------------------
	// 
	// 2014.11.12:
	// First check-in.
	// 
	// =========================================================================
?>
