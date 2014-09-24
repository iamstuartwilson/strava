<?php

namespace Strava;

	/**
	* Simple PHP Library for the Strava v3 API
	* @author Stuart Wilson <stuart@iamstuartwilson.com>
	* @link https://github.com/iamstuartwilson/strava
	* @since 18/02/2014
	*/

	class StravaApi{

		/**
		 * Sets up the class with the $clientId and $clientSecret
		 * @param int $clientId
		 * @param string $clientSecret
		 */

		public function __construct( $clientId, $clientSecret ){

			$this->clientId = $clientId;
			$this->clientSecret = $clientSecret;
			$this->baseUrl = 'https://www.strava.com/';
			$this->apiUrl = $this->baseUrl . 'api/v3/';
			$this->authUrl = $this->baseUrl . 'oauth/';

		}

		/**
		 * Appends query array onto URL
		 * @param string $url
		 * @param array $query
		 * @return string
		 */

		protected function parseGet( $url, $query ){

			$append = strpos( $url, '?' ) === false ? '?' : '&';

   			return $url . $append . http_build_query( $query );

		}

		/**
		 * Parses JSON as PHP object
		 * @param string $response
		 * @return object
		 */

		protected function parseResponse( $response ){

			return json_decode( $response );

		}

		/**
		 * Makes HTTP Request to the API
		 * @param string $url
		 * @param array $parameters
		 * @return mixed
		 */

		protected function request( $url, $parameters = array(), $request = false ){

			$this->lastRequest = $url;
			$this->lastRequestData = $parameters;
			$curl = curl_init( $url );
			$curlOptions = array(
				CURLOPT_SSL_VERIFYPEER	=> false,
				CURLOPT_FOLLOWLOCATION 	=> true,
				CURLOPT_REFERER 		=> $url,
				CURLOPT_RETURNTRANSFER 	=> true
			);

			if( ! empty( $parameters ) || ! empty( $request ) ){

				if( ! empty( $request ) ){

					$curlOptions[ CURLOPT_CUSTOMREQUEST ] = $request;
					$parameters = http_build_query( $parameters );

				}

				else{

					$curlOptions[ CURLOPT_POST ] = true;

				}

				$curlOptions[ CURLOPT_POSTFIELDS ] = $parameters;

			}

			curl_setopt_array( $curl, $curlOptions );
	        $response = curl_exec( $curl );
	        $error = curl_error( $curl );
	        $this->lastRequestInfo = curl_getinfo( $curl );
	        curl_close( $curl );

	       	if( ! $response ){

	       		return $error;

	       	}

	       	else{

	       		return $this->parseResponse( $response );

	       	}

		}

		/**
		 * Creates authentication URL for your app
		 * @param string $redirect
		 * @param string $approvalPrompt
		 * @param string $scope
		 * @param string $state
		 * @link http://strava.github.io/api/v3/oauth/#get-authorize
		 * @return string
		 */

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

		/**
		 * Authenticates token returned from API
		 * @param string $code
		 * @link http://strava.github.io/api/v3/oauth/#post-token
		 * @return function
		 */

		public function tokenExchange( $code ){

			$parameters = array(
				'client_id'		=> $this->clientId,
				'client_secret' => $this->clientSecret,
				'code'			=> $code
			);

			return $this->request( $this->authUrl . 'token', $parameters );

		}

		/**
		 * Deauthorises application
		 * @param string $accessToken
		 * @link http://strava.github.io/api/v3/oauth/#deauthorize
		 * @return function
		 */

		public function deauthorize( $accessToken ){

			$parameters = array(
				'access_token'	=> $accessToken
			);

			return $this->request( $this->authUrl . 'deauthorize', $parameters );

		}

		/**
		 * Sends GET request to specified API endpoint
		 * @param string $request
		 * @param string $accessToken
		 * @param array $parameters
		 * @example http://strava.github.io/api/v3/athlete/#koms
		 * @return function
		 */

		public function get( $request, $accessToken, $parameters = array() ){

			$parameters = array_merge( $parameters, array( 'access_token' => $accessToken ) );
			$requestUrl = $this->parseGet( $this->apiUrl . $request, $parameters );

			return $this->request( $requestUrl );

		}

		/**
		 * Sends PUT request to specified API endpoint
		 * @param string $request
		 * @param string $accessToken
		 * @param array $parameters
		 * @example http://strava.github.io/api/v3/athlete/#update
		 * @return function
		 */

		public function put( $request, $accessToken, $parameters = array() ){

			$parameters = array_merge( $parameters, array( 'access_token' => $accessToken ) );

			return $this->request( $this->apiUrl . $request, $parameters, 'PUT' );

		}

		/**
		 * Sends POST request to specified API endpoint
		 * @param string $request
		 * @param string $accessToken
		 * @param array $parameters
		 * @example http://strava.github.io/api/v3/activities/#create
		 * @return function
		 */

		public function post( $request, $accessToken, $parameters = array() ){

			$parameters = array_merge( $parameters, array( 'access_token' => $accessToken ) );

			return $this->request( $this->apiUrl . $request, $parameters );

		}

		/**
		 * Sends DELETE request to specified API endpoint
		 * @param string $request
		 * @param string $accessToken
		 * @param array $parameters
		 * @example http://strava.github.io/api/v3/activities/#delete
		 * @return function
		 */

		public function delete( $request, $accessToken, $parameters = array() ){

			$parameters = array_merge( $parameters, array( 'access_token' => $accessToken ) );

			return $this->request( $this->apiUrl . $request, $parameters, 'DELETE' );

		}

	}
	