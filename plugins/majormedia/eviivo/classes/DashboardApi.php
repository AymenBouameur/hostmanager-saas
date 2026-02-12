<?php

namespace MajorMedia\Eviivo\Classes;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use MajorMedia\ToolBox\Classes\ClientAPI;
use GuzzleHttp\Exception\RequestException;
use October\Rain\Exception\ApplicationException;

class DashboardApi extends ClientAPI
{
    protected const PROVIDER_URL = "https://io.eviivo.com/pms";
    protected const PROVIDER_TYPE = 'REST';
    protected const TOKEN_URL = "https://auth.eviivo.com/api/connect/token";
    protected const CLIENT_ID = "7dafec79-338d-42c4-9b83-f0978ea0fe42";
    protected const CLIENT_SECRET = "b7K0tnbbjtzK0sWazXur";
    protected const GRANT_TYPE = "client_credentials";

    protected $accessToken;

    public function __construct()
    {
        parent::__construct();
        if ($this->clientAPI === null) {
            $this->clientAPI = new \GuzzleHttp\Client();
        }
        // Check for cached token or authenticate
        if (!Cache::has('eviivo_access_token')) {
            $this->authenticate();
        } else {
            $this->accessToken = Cache::get('eviivo_access_token');
        }

        // Set the necessary headers
        $this->pushHeader([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/json',
            'X-Auth-ClientId' => self::CLIENT_ID,
        ]);
    }

    /**
     * Authenticate and get access token.
     */
    protected function authenticate()
    {
        try {
            $response = $this->clientAPI->post(self::TOKEN_URL, [
                'form_params' => [
                    'client_id' => self::CLIENT_ID,
                    'client_secret' => self::CLIENT_SECRET,
                    'grant_type' => self::GRANT_TYPE,
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if (isset($data['access_token'])) {
                Cache::put('eviivo_access_token', $data['access_token'], $data['expires_in']);
                $this->accessToken = $data['access_token'];
            } else {
                throw new ApplicationException('Failed to retrieve access token');
            }
        } catch (RequestException $e) {
            throw new ApplicationException("Authentication Error: " . $e->getMessage());
        }
    }

    /**
     * Make an API request (GET, POST, etc.)
     */
    public function request($method, $endpoint, $params = [])
    {
        if (!in_array(strtolower($method), ['get', 'post', 'put', 'delete'])) {
            throw new ApplicationException("Invalid request method: $method");
        }

        $this->setupClientAPI();

        // Set the options for the request
        $options = [
            'headers' => $this->headers,
            'debug' => false,
        ];

        // Add parameters based on the method
        if (!empty($params)) {
            if ($method === 'get') {
                $options['query'] = $params;
            } else {
                $options['json'] = $params;
            }
        }


        $url = self::PROVIDER_URL . '/' . $endpoint;

        try {
            $response = $this->clientAPI->{strtolower($method)}($url, $options);
            $this->response = $response;
            return json_decode($response->getBody()->getContents(), true);
        } catch (ServerException $ex) {
            dd($ex->getResponse()->getBody()->getContents());
            throw new ApplicationException("Server error: " . $ex->getMessage());
        } catch (ClientException $ex) {
            throw new ApplicationException("Client error: " . $ex->getMessage());
        }
    }

    /**
     * Converts response to array.
     */
    public function toArray()
    {
        if (!$this->response instanceof Response) {
            throw new ApplicationException("Response must be an instance of 'GuzzleHttp\Psr7\Response'!");
        }
        return json_decode($this->response->getBody()->getContents(), true);
    }
    /**
     * Wait for the rate limit reset by delaying the request.
     */
    protected function waitForRateLimitReset($lastRequestTime)
    {
        $minutesRemaining = Carbon::now()->diffInMinutes(Carbon::parse($lastRequestTime));

        if ($minutesRemaining < 60) {
            $delay = 60 - $minutesRemaining;
            Log::info("Waiting for {$delay} minutes to reset rate limit.");
            sleep($delay * 60);
        }
    }
}
