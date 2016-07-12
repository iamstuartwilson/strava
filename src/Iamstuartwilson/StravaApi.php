<?php

    namespace Iamstuartwilson;

    /**
     * Simple PHP Library for the Strava v3 API
     *
     * @author Stuart Wilson <bonjour@iamstuartwilson.com>
     *
     * @link https://github.com/iamstuartwilson/strava
     */

    class StravaApi
    {
        const BASE_URL = 'https://www.strava.com/';

        public $lastRequest;
        public $lastRequestData;
        public $lastRequestInfo;

        /**
         * Stores the HTTP headers from the last API response, e. g.:
         *
         * [
         *     'Cache-Control'     => 'max-age=0, private, must-revalidate',
         *     'X-RateLimit-Limit' => '600,30000',
         *     'X-RateLimit-Usage' => '4,25',
         *     'Content-Length'    => '2031',
         *     ...
         * ]
         *
         * Access with the `getResponseHeader()` or `getResponseHeaders()` methods.
         *
         * @var array
         */
        protected $responseHeaders = array();

        protected $apiUrl;
        protected $authUrl;
        protected $clientId;
        protected $clientSecret;

        private $accessToken;

        /**
         * Sets up the class with the $clientId and $clientSecret
         *
         * @param int    $clientId
         * @param string $clientSecret
         */
        public function __construct($clientId = 1, $clientSecret = '')
        {
            $this->clientId     = $clientId;
            $this->clientSecret = $clientSecret;
            $this->apiUrl       = self::BASE_URL . 'api/v3/';
            $this->authUrl      = self::BASE_URL . 'oauth/';
        }

        /**
         * Returns the complete list of response headers.
         *
         * @return array
         */
        public function getResponseHeaders()
        {
            return $this->responseHeaders;
        }

        /**
         * @param string $header
         *
         * @return string
         */
        public function getResponseHeader($header)
        {
            if (! isset($this->responseHeaders[$header])) {
                throw new \InvalidArgumentException('Header does not exist');
            }

            return $this->responseHeaders[$header];
        }

        /**
         * Appends query array onto URL
         *
         * @param string $url
         * @param array  $query
         *
         * @return string
         */
        protected function parseGet($url, $query)
        {
            $append = strpos($url, '?') === false ? '?' : '&';

            return $url . $append . http_build_query($query);
        }

        /**
         * Parses JSON as PHP object
         *
         * @param string $response
         *
         * @return object
         */
        protected function parseResponse($response)
        {
            return json_decode($response);
        }

        /**
         * Makes HTTP Request to the API
         *
         * @param string $url
         * @param array $parameters
         * @param bool|string $request the request method, default is POST
         *
         * @return mixed
         * @throws \Exception
         */
        protected function request($url, $parameters = array(), $request = false)
        {
            $this->lastRequest = $url;
            $this->lastRequestData = $parameters;
            $this->responseHeaders = array();

            $curl = curl_init($url);

            $curlOptions = array(
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_REFERER        => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADERFUNCTION => array($this, 'parseHeader'),
            );

            if (! empty($parameters) || ! empty($request)) {
                if (! empty($request)) {
                    $curlOptions[ CURLOPT_CUSTOMREQUEST ] = $request;
                    $parameters = http_build_query($parameters);
                } else {
                    $curlOptions[ CURLOPT_POST ] = true;
                }

                $curlOptions[ CURLOPT_POSTFIELDS ] = $parameters;
            }

            curl_setopt_array($curl, $curlOptions);

            $response = curl_exec($curl);
            $error    = curl_error($curl);

            $this->lastRequestInfo = curl_getinfo($curl);

            curl_close($curl);

            if (! empty($error)) {
                throw new \Exception($error);
            }

            return $this->parseResponse($response);
        }

        /**
         * Creates authentication URL for your app
         *
         * @param string $redirect
         * @param string $approvalPrompt
         * @param string $scope
         * @param string $state
         *
         * @link http://strava.github.io/api/v3/oauth/#get-authorize
         *
         * @return string
         */
        public function authenticationUrl($redirect, $approvalPrompt = 'auto', $scope = null, $state = null)
        {
            $parameters = array(
                'client_id'       => $this->clientId,
                'redirect_uri'    => $redirect,
                'response_type'   => 'code',
                'approval_prompt' => $approvalPrompt,
                'state'           => $state,
            );

            if (! is_null($scope)) {
                $parameters['scope'] = $scope;
            }

            return $this->parseGet(
                $this->authUrl . 'authorize',
                $parameters
            );
        }

        /**
         * Authenticates token returned from API
         *
         * @param string $code
         *
         * @link http://strava.github.io/api/v3/oauth/#post-token
         *
         * @return string
         */
        public function tokenExchange($code)
        {
            $parameters = array(
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code'          => $code,
            );

            return $this->request(
                $this->authUrl . 'token',
                $parameters
            );
        }

        /**
         * Deauthorises application
         *
         * @link http://strava.github.io/api/v3/oauth/#deauthorize
         *
         * @return string
         */
        public function deauthorize()
        {
            return $this->request(
                $this->authUrl . 'deauthorize',
                $this->generateParameters(array())
            );
        }

        /**
         * Sets the access token used to authenticate API requests
         *
         * @param string $token
         *
         * @return string
         */
        public function setAccessToken($token)
        {
            return $this->accessToken = $token;
        }

        /**
         * Sends GET request to specified API endpoint
         *
         * @param string $request
         * @param array  $parameters
         *
         * @example http://strava.github.io/api/v3/athlete/#koms
         *
         * @return string
         */
        public function get($request, $parameters = array())
        {
            $parameters = $this->generateParameters($parameters);
            $requestUrl = $this->parseGet($this->apiUrl . $request, $parameters);

            return $this->request($requestUrl);
        }

        /**
         * Sends PUT request to specified API endpoint
         *
         * @param string $request
         * @param array  $parameters
         *
         * @example http://strava.github.io/api/v3/athlete/#update
         *
         * @return string
         */
        public function put($request, $parameters = array())
        {
            return $this->request(
                $this->apiUrl . $request,
                $this->generateParameters($parameters),
                'PUT'
            );
        }

        /**
         * Sends POST request to specified API endpoint
         *
         * @param string $request
         * @param array  $parameters
         *
         * @example http://strava.github.io/api/v3/activities/#create
         *
         * @return string
         */
        public function post($request, $parameters = array())
        {

            return $this->request(
                $this->apiUrl . $request,
                $this->generateParameters($parameters)
            );
        }

        /**
         * Sends DELETE request to specified API endpoint
         *
         * @param string $request
         * @param array  $parameters
         *
         * @example http://strava.github.io/api/v3/activities/#delete
         *
         * @return string
         */
        public function delete($request, $parameters = array())
        {
            return $this->request(
                $this->apiUrl . $request,
                $this->generateParameters($parameters),
                'DELETE'
            );
        }

        /**
         * Adds access token to paramters sent to API
         *
         * @param  array $parameters
         *
         * @return array
         */
        protected function generateParameters($parameters)
        {
            return array_merge(
                $parameters,
                array( 'access_token' => $this->accessToken )
            );
        }

        /**
         * Parses the header lines into the $responseHeaders attribute
         *
         * Skips the first header line (HTTP response status) and the last header
         * line (empty).
         *
         * @param resource $curl
         * @param string $headerLine
         *
         * @return int length of the currently parsed header line in bytes
         */
        protected function parseHeader($curl, $headerLine)
        {
            $size    = strlen($headerLine);
            $trimmed = trim($headerLine);

            // skip empty line(s)
            if (empty($trimmed)) {
                return $size;
            }

            // skip first header line (HTTP status code)
            if (strpos($trimmed, 'HTTP/') === 0) {
                return $size;
            }

            $parts = explode(':', $headerLine);
            $key   = array_shift($parts);
            $value = implode(":", $parts);

            $this->responseHeaders[$key] = trim($value);

            return $size;
        }
    }
