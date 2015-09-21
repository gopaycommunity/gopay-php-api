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
        return $this->api(
            'oauth2/token',
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'auth' => [$this->config['clientID'], $this->config['clientSecret']]
            ],
            ['grant_type' => 'client_credentials', 'scope' => $scope]
        );
    }

    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    public function createPayment(array $payment)
    {
        return $this->api('payments/payment', Browser::JSON, $payment);
    }

    public function getStatus($id)
    {
        return $this->api("payments/payment/{$id}", Browser::FORM);
    }

    public function refund($id, $amount)
    {
        return $this->api("payments/payment/{$id}/refund", Browser::FORM, ['amount' => $amount]);
    }

    private function api($urlPath, $headersOrContentType, array $data = array())
    {
        if (is_array($headersOrContentType)) {
            $headers = $headersOrContentType;
        } else {
            $headers = [
                'Content-Type' => $headersOrContentType,
                'Authorization' => "Bearer {$this->accessToken}"
            ];
        }
        return $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/{$urlPath}",
            $data,
            $headers + ['Accept' => 'application/json']
        );
    }
}
