<?php

	/**
	* PHP Library for the Strava v3 API
	* 
	*/

	class StravaApi{

		public $clientId;
		public $clientSecret;
		public $baseUrl = 'https://www.strava.com/';
		public $apiUrl;
		public $authUrl;
		public $lastRequest;
		public $lastPostRequest;

		public function __construct( $clientId, $clientSecret ){

			$this->clientId = $clientId;
			$this->clientSecret = $clientSecret;
			$this->apiUrl = $this->baseUrl . 'api/v3/';
			$this->authUrl = $this->baseUrl . 'oauth/';

		}

		protected function parseGet( $url, $query ){

			$append = strpos( $url, '?' ) === false ? '?' : '&';

   			return $url . $append . http_build_query( $query );

		}

		protected function parseResponse( $response ){

			return json_decode( $response );

		}

		protected function request( $url, $parameters = array() ){

			$this->lastRequest = $url;
			$curl = curl_init( $url );
			$curlOptions = array(
				CURLOPT_SSL_VERIFYPEER	=> false,
				CURLOPT_FOLLOWLOCATION 	=> true,
				CURLOPT_REFERER 		=> $url,
				CURLOPT_RETURNTRANSFER 	=> true,
			);

			if( ! empty( $parameters ) ){

				$this->lastPostRequest = $parameters;
				$curlOptions[ CURLOPT_POST ] = true;
				$curlOptions[ CURLOPT_POSTFIELDS ] = $parameters;

			}

			curl_setopt_array( $curl, $curlOptions );
	        $response = curl_exec( $curl );
	        $error = curl_error( $curl );
	        curl_close( $curl );

	       	if( ! $response ){

	       		return $error;

	       	}

	       	else{

	       		return $this->parseResponse( $response );

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

		//ATHLETE METHODS

		//http://strava.github.io/api/v3/athlete/#get-another-details +
		//http://strava.github.io/api/v3/athlete/#get-details
		public function getAthlete( $accessToken, $athleteId = null ){

			$parameters = array(
				'access_token'	=> $accessToken
			);

			$url = $this->apiUrl . 'athlete';
			if( ! empty( $athleteId ) ) $url .= 's/' . $athleteId;

			$requestUrl = $this->parseGet( $url, $parameters );

			return $this->request( $requestUrl );

		}

		//Generic user/athlete detail method
		protected function getUsers( $userType, $accessToken, $athleteId, $page, $perPage ){

			$parameters = array(
				'access_token'	=> $accessToken,
				'page'			=> $page,
				'per_page'		=> $perPage
			);

			$url = $this->apiUrl . 'athlete';
			if( ! empty( $athleteId ) ) $url .= 's/' . $athleteId;

			$requestUrl = $this->parseGet( $url . '/' . $userType, $parameters );

			return $this->request( $requestUrl );

		}

		//http://strava.github.io/api/v3/follow/#friends
		public function getFriends( $accessToken, $athleteId = null, $page = 1, $perPage = 30 ){

			return $this->getUsers( 'friends', $accessToken, $athleteId, $page, $perPage );

		}

		//http://strava.github.io/api/v3/follow/#followers
		public function getFollowers( $accessToken, $athleteId = null, $page = 1, $perPage = 30 ){

			return $this->getUsers( 'followers', $accessToken, $athleteId, $page, $perPage );

		}

		//http://strava.github.io/api/v3/follow/#both
		public function getBothFollowing( $accessToken, $athleteId, $page = 1, $perPage = 30 ){

			return $this->getUsers( 'both-following', $accessToken, $athleteId, $page, $perPage );

		}

		//http://strava.github.io/api/v3/athlete/#koms
		public function getKoms( $accessToken, $athleteId, $page = 1, $perPage = 30 ){

			return $this->getUsers( 'koms', $accessToken, $athleteId, $page, $perPage );

		}

	}