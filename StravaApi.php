<?php

	class StravaApi{

		private $clientId;
		private $clientSecret;
		private $baseUrl = 'https://www.strava.com/';
		private $apiUrl;
		private $authUrl;

		public function __construct( $clientId, $clientSecret ){

			$this->clientId = $clientId;
			$this->clientSecret = $clientSecret;
			$this->apiUrl = $this->baseUrl . 'api/v3/';
			$this->authUrl = $this->baseUrl . 'oauth/';

		}

		private function parseGet( $url, $query ){

			$append = strpos( $url, '?' ) === false ? '?' : '&';

   			return $url . $append . http_build_query( $query );

		}

		private function request( $url, $parameters = array() ){

			$curl = curl_init( $url );
			$curlOptions = array(
				CURLOPT_SSL_VERIFYPEER	=> false,
				CURLOPT_FOLLOWLOCATION 	=> true,
				CURLOPT_REFERER 		=> $url,
				CURLOPT_RETURNTRANSFER 	=> true,
			);

			if( ! empty( $parameters ) ){

				$curlOptions[ CURLOPT_POST ] = true;
				$curlOptions[ CURLOPT_POSTFIELDS ] = $parameters;

			}

			curl_setopt_array( $curl, $curlOptions );
	        $response = curl_exec( $curl );
	        //curl_close( $curl );

	       	if( ! $response ){

	       		return curl_error( $curl );

	       	}

	       	else{

	       		return $response;

	       	}

		}

		public function authenticationUrl( $redirect, $approvalPrompt = 'auto', $scope = null, $state = null ){

			$parameters = array(
				'client_id'			=> $this->clientId,
				'redirect_uri'		=> $redirect,
				'response_type'		=> 'code',
				'approval_prompt'	=> $approvalPrompt,
				'scope'				=> $scope,
				'state'				=> $state
			);

			if( ! empty( $scope ) ) $parameters['scope'] = $scope;
			if( ! empty( $state ) ) $parameters['state'] = $state;

			return $this->parseGet( $this->authUrl . 'authorize', $parameters );

		}

		public function tokenExchange( $code ){

			$parameters = array(
				'client_id'		=> $this->clientId,
				'client_secret' => $this->clientSecret,
				'code'			=> $code
			);

			return $this->request( $this->authUrl . 'token', $parameters );

		}

	}