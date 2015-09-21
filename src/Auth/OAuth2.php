<?php

namespace GoPay\Auth;

use GoPay\Http\Browser;

class OAuth2
{
    private $config;
    private $browser;
    private $cache;

    public function __construct(array $config, TokenCache $c, Browser $b)
    {
        $this->config = $config;
        $this->cache = $c;
        $this->browser = $b;
    }

    public function getAccessToken()
    {
        if ($this->cache->isExpired()) {
            $this->authorize();
        }
        return $this->cache->getAccessToken();
    }

    private function authorize()
    {
        $response = $this->api(
            'oauth2/token',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => [$this->config['clientID'], $this->config['clientSecret']]
            ],
            ['grant_type' => 'client_credentials', 'scope' => $this->config['scope']]
        );
        if ($response->hasSucceed()) {
            $accessToken = $response->json['access_token'];
            $expirationDate = new \DateTime("now + {$response->json['expires_in']} seconds");
            $this->cache->setAccessToken($accessToken, $expirationDate);
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
