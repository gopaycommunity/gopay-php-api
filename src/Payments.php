<?php

namespace GoPay;

class Payments
{
    private $gopay;
    private $auth;

    public function __construct(GoPay $g, OAuth2 $a)
    {
        $this->gopay = $g;
        $this->auth = $a;
    }

    public function createPayment(array $payment)
    {
        return $this->api('', GoPay::JSON, $payment);
    }

    public function getStatus($id)
    {
        return $this->api("/{$id}", GoPay::FORM);
    }

    public function refund($id, $amount)
    {
        return $this->api("/{$id}/refund", GoPay::FORM, ['amount' => $amount]);
    }

    public function recurrenceOnDemand($id, array $payment)
    {
        return $this->api("/{$id}/create-recurrence", GoPay::JSON, $payment);
    }

    public function recurrenceVoid($id)
    {
        return $this->api("/{$id}/void-recurrence", GoPay::FORM, array());
    }

    public function preauthorizedCapture($id)
    {
        return $this->api("/{$id}/capture", GoPay::FORM, array());
    }

    public function preauthorizedVoid($id)
    {
        return $this->api("/{$id}/void-authorization", GoPay::FORM, array());
    }

    /** @return \GoPay\Http\Response */
    private function api($urlPath, $contentType, $data = null)
    {
        $token = $this->auth->getAccessToken();
        if ($token->token) {
            return $this->gopay->call(
                "payments/payment{$urlPath}",
                [
                    'Accept' => 'application/json',
                    'Content-Type' => $contentType,
                    'Authorization' => "Bearer {$token->token}"
                ],
                $data
            );
        }
        return $token->response;
    }
}
