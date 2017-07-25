<?php

namespace GoPay;

use GoPay\Definition\RequestMethods;
use GoPay\Token\AccessToken;

class OAuth2 implements Auth
{
    private $gopay;

    public function __construct(GoPay $g)
    {
        $this->gopay = $g;
    }

    public function authorize()
    {
        $credentials = "{$this->gopay->getConfig('clientId')}:{$this->gopay->getConfig('clientSecret')}";
        $response = $this->gopay->call(
            'oauth2/token',
            GoPay::FORM,
            'Basic ' . base64_encode($credentials),
            RequestMethods::POST,
            ['grant_type' => 'client_credentials', 'scope' => $this->gopay->getConfig('scope')]
        );
        $t = new AccessToken;
        $t->response = $response;
        if ($response->hasSucceed()) {
            $t->token = $response->json['access_token'];
            $t->expirationDate = new \DateTime("now + {$response->json['expires_in']} seconds");
        }
        return $t;
    }

    public function getClient()
    {
        $ids = [
            $this->gopay->getConfig('clientId'),
            (int) $this->gopay->getConfig('isProductionMode'),
            $this->gopay->getConfig('scope'),
        ];
        return implode('-', $ids);
    }
}
