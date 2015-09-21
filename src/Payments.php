<?php

namespace GoPay;

class Payments
{
    private $config;
    private $browser;

    private $accessToken;

    public function __construct(array $config, Browser $b)
    {
        $this->config = $config;
        $this->browser = $b;
    }

    public function authorize($scope)
    {
        return $this->callApi(
            'oauth2/token',
            ['grant_type' => 'client_credentials', 'scope' => $scope],
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'auth' => [$this->config['clientID'], $this->config['clientSecret']]
            ],
            'getOAuthToken'
        );
    }

    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    public function createPayment(array $payment)
    {
        return $this->callApi(
            'payments/payment',
            $payment,
            [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->accessToken}"
            ]
        );
    }

    public function getStatus($id)
    {
        return $this->callApi(
            "payments/payment/{$id}",
            [],
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$this->accessToken}"
            ]
        );
    }

    public function refund($id, $amount)
    {
        return $this->callApi(
            "payments/payment/{$id}/refund",
            ['amount' => $amount],
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$this->accessToken}"
            ]
        );
    }

    private function callApi($urlPath, array $data, array $headers)
    {
        return $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/{$urlPath}",
            $data,
            $headers + ['Accept' => 'application/json']
        );
    }
}
