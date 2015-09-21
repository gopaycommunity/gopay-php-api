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
        list($statusCode, $json) = $this->browser->getOAuthToken(
            'https://gw.sandbox.gopay.com/api/oauth2/token',
            "grant_type=client_credentials&scope={$scope}",
            ['auth' => [$this->config['clientID'], $this->config['clientSecret']]]
        );
        $r = new Response;
        $r->hasSucceed = $statusCode == 200;
        $r->json = $json;
        return $r;
    }

    public function createPayment(array $payment, $token)
    {
        list($statusCode, $json) = $this->browser->postJson(
            'https://gw.sandbox.gopay.com/api/payments/payment',
            $payment,
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ]
        );
        $r = new Response;
        $r->hasSucceed = $statusCode == 200;
        $r->json = $json;
        return $r;
    }

    public function getStatus($id, $token)
    {
        list($statusCode, $json) = $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/payments/payment/{$id}",
            [],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$token}"
            ]
        );
        $r = new Response;
        $r->hasSucceed = $statusCode == 200;
        $r->json = $json;
        return $r;
    }

    public function refund($id, $amount, $token)
    {
        list($statusCode, $json) = $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/payments/payment/{$id}/refund",
            ['amount' => $amount],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$token}"
            ]
        );
        $r = new Response;
        $r->hasSucceed = $statusCode == 200;
        $r->json = $json;
        return $r;
    }
}
