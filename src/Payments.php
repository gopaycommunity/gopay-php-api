<?php

namespace GoPay;

class Payments
{
    private $config;
    private $browser;

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

    public function createPayment(array $payment, $token)
    {
        return $this->callApi(
            'payments/payment',
            $payment,
            [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ]
        );
    }

    public function getStatus($id, $token)
    {
        return $this->callApi(
            "payments/payment/{$id}",
            [],
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$token}"
            ]
        );
    }

    public function refund($id, $amount, $token)
    {
        return $this->callApi(
            "payments/payment/{$id}/refund",
            ['amount' => $amount],
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$token}"
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
