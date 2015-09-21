<?php

namespace GoPay\Auth;

use GoPay\GoPay;

class OAuth2
{
    private $gopay;
    private $cache;

    public function __construct(GoPay $g, TokenCache $c)
    {
        $this->gopay = $g;
        $this->cache = $c;
    }

    public function getAccessToken()
    {
        $scope = $this->gopay->getConfig('scope');
        $this->cache->setScope($scope);
        if ($this->cache->isExpired()) {
            $this->authorize($scope);
        }
        return $this->cache->getAccessToken();
    }

    private function authorize($scope)
    {
        $response = $this->gopay->call(
            'oauth2/token',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => [$this->gopay->getConfig('clientID'), $this->gopay->getConfig('clientSecret')]
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
