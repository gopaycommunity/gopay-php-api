<?php

namespace GoPay;

use GoPay\Http\Browser;

class OAuth2
{
    private $config;
    private $browser;

    private $accessToken;

    public function __construct(array $config, Browser $b)
    {
        $this->config = $config;
        $this->browser = $b;
    }

    public function getAccessToken()
    {
        if (!$this->accessToken) {
            $this->authorize();
        }
        return $this->accessToken;
    }

    private function authorize()
    {
        $response = $this->api(
            'oauth2/token',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'auth' => [$this->config['clientID'], $this->config['clientSecret']]
            ],
            ['grant_type' => 'client_credentials', 'scope' => $this->config['scope']]
        );
        if ($response->hasSucceed()) {
            $this->accessToken = $response->json['access_token'];
        }
    }

    private function api($urlPath, $headers, array $data)
    {
        return $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/{$urlPath}",
            $headers,
            $data
        );
    }
}
