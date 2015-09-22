<?php

namespace GoPay;

use GoPay\Token\TokenCache;
use GoPay\Token\AccessToken;

class OAuth2
{
    private $gopay;
    private $cache;

    public function __construct(GoPay $g, TokenCache $c)
    {
        $this->gopay = $g;
        $this->cache = $c;
    }

    /** @return AccessToken */
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
            Gopay::FORM,
            [$this->gopay->getConfig('clientID'), $this->gopay->getConfig('clientSecret')],
            ['grant_type' => 'client_credentials', 'scope' => $scope]
        );
        $t = new AccessToken;
        $t->response = $response;
        if ($response->hasSucceed()) {
            $t->token = $response->json['access_token'];
            $t->expirationDate = new \DateTime("now + {$response->json['expires_in']} seconds");
        }
        $this->cache->setAccessToken($t);
    }
}
