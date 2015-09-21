<?php

namespace GoPay;

use GoPay\Http\Browser;

class Payments
{
    private $config;
    private $auth;
    private $browser;

    public function __construct(array $config, OAuth2 $a, Browser $b)
    {
        $this->config = $config;
        $this->auth = $a;
        $this->browser = $b;
    }

    public function createPayment(array $payment)
    {
        return $this->api('', Browser::JSON, $payment);
    }

    public function getStatus($id)
    {
        return $this->api("/{$id}", Browser::FORM);
    }

    public function refund($id, $amount)
    {
        return $this->api("/{$id}/refund", Browser::FORM, ['amount' => $amount]);
    }

    private function api($urlPath, $contentType, $data = null)
    {
        $method = is_array($data) ? 'postJson' : 'getJson';
        $this->browser->setBaseUrl($this->config['isProductionMode']);
        return $this->browser->{$method}(
            "payments/payment{$urlPath}",
            [
                'Accept' => 'application/json',
                'Content-Type' => $contentType,
                'Authorization' => "Bearer {$this->auth->getAccessToken()}"
            ],
            $data
        );
    }
}
