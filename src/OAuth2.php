<?php

namespace GoPay;

use GoPay\Token\AccessToken;

class OAuth2
{
    private $gopay;

    public function __construct(GoPay $g)
    {
        $this->gopay = $g;
    }

    public function authorize()
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
        return $t;
    }
}
