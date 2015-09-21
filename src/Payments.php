<?php

namespace GoPay;

use GoPay\Http\GopayBrowser;
use GoPay\Auth\OAuth2;

class Payments
{
    private $auth;
    private $browser;

    public function __construct(GopayBrowser $b, OAuth2 $a)
    {
        $this->browser = $b;
        $this->auth = $a;
    }

    public function createPayment(array $payment)
    {
        return $this->api('', GopayBrowser::JSON, $payment);
    }

    public function getStatus($id)
    {
        return $this->api("/{$id}", GopayBrowser::FORM);
    }

    public function refund($id, $amount)
    {
        return $this->api("/{$id}/refund", GopayBrowser::FORM, ['amount' => $amount]);
    }

    public function createRecurrencePayment(array $payment)
    {
        return $this->api('', GopayBrowser::JSON, $payment);
    }

    public function recurrenceOnDemand($id, array $payment)
    {
        return $this->api("/{$id}/create-recurrence", GopayBrowser::JSON, $payment);
    }

    public function recurrenceVoid($id)
    {
        return $this->api("/{$id}/void-recurrence", GopayBrowser::FORM, array());
    }

    public function createPreauthorizedPayment(array $payment)
    {
        return $this->api('', GopayBrowser::JSON, $payment);
    }

    public function preauthorizedCapture($id)
    {
        return $this->api("/{$id}/capture", GopayBrowser::FORM, array());
    }

    public function preauthorizedVoid($id)
    {
        return $this->api("/{$id}/void-authorization", GopayBrowser::FORM, array());
    }

    private function api($urlPath, $contentType, $data = null)
    {
        return $this->browser->api(
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
