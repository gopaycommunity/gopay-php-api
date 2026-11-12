<?php

namespace GoPay;

use GoPay\Definition\RequestMethods;
use GoPay\Token\AccessToken;

class OAuth2 implements Auth
{
    private GoPay $gopay;

    public function __construct(GoPay $g)
    {
        $this->gopay = $g;
    }

    /**
     * @return AccessToken
     */
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
        $t = new AccessToken();
        $t->response = $response;
        if ($response->hasSucceed()) {
            // Successful token response is always JSON with access_token and expires_in
            $t->token = $response->json['access_token']; // @phpstan-ignore offsetAccess.notFound
            $expSuffix = '';
            if ($response->json['expires_in'] > 0) { // @phpstan-ignore offsetAccess.notFound
                $expSuffix .= " + {$response->json['expires_in']} seconds";
            }
            $t->expirationDate = new \DateTime("now {$expSuffix}");
        }
        return $t;
    }

    /**
     * Unique identifier of current client (client, environment, scope) used as token cache key.
     *
     * @return string
     */
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
