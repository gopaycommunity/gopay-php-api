<?php

namespace GoPay\Auth;

use GoPay\Http\GopayBrowser;

class OAuth2
{
    private $config;
    private $browser;
    private $cache;

    public function __construct(GopayBrowser $b, TokenCache $c)
    {
        $this->browser = $b;
        $this->cache = $c;
    }

    public function getAccessToken()
    {
        $scope = $this->browser->getConfig('scope');
        $this->cache->setScope($scope);
        if ($this->cache->isExpired()) {
            $this->authorize($scope);
        }
        return $this->cache->getAccessToken();
    }

    private function authorize($scope)
    {
        $response = $this->browser->api(
            'oauth2/token',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => [$this->browser->getConfig('clientID'), $this->browser->getConfig('clientSecret')]
            ],
            ['grant_type' => 'client_credentials', 'scope' => $scope]
        );
        if ($response->hasSucceed()) {
            $accessToken = $response->json['access_token'];
            $expirationDate = new \DateTime("now + {$response->json['expires_in']} seconds");
            $this->cache->setAccessToken($accessToken, $expirationDate);
        }
    }
}
