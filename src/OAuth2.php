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
            '/oauth2/token',
            'Basic ' . base64_encode($credentials),
            RequestMethods::POST,
            GoPay::FORM,
            ['grant_type' => 'client_credentials', 'scope' => $this->gopay->getConfig('scope')]
        );
        $t = new AccessToken;
        $t->response = $response;
        if ($response->hasSucceed()) {
            $t->token = $response->json['access_token'];
            $expSuffix = "";
            if ($response->json['expires_in'] > 0) {
                $expSuffix .= " + {$response->json['expires_in']} seconds";
            }
            $t->expirationDate = new \DateTime("now {$expSuffix}");
        }
        return $t;
    }

    public function getClient()
    {
        $ids = [
            $this->gopay->getConfig('clientId'),
            $this->gopay->getConfig('gatewayUrl'),
            $this->gopay->getConfig('scope'),
        ];
        return implode('-', $ids);
    }
}
