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
        $this->loadCurrentClient();
        if ($this->cache->isExpired()) {
            $this->authorize();
        }
        return $this->cache->getAccessToken();
    }

    public function loadCurrentClient()
    {
        $ids = [
            $this->gopay->getConfig('clientId'),
            (int) $this->gopay->getConfig('isProductionMode'),
            $this->gopay->getConfig('scope'),
        ];
        $client = implode('-', $ids);
        $this->cache->setClient($client);
    }

    private function authorize()
    {
        $response = $this->gopay->call(
            'oauth2/token',
            GoPay::FORM,
            [$this->gopay->getConfig('clientId'), $this->gopay->getConfig('clientSecret')],
            ['grant_type' => 'client_credentials', 'scope' => $this->gopay->getConfig('scope')]
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
