<?php

namespace GoPay;

class Payments
{
    private $gopay;
    private $auth;

    public function __construct(GoPay $g, Auth $a)
    {
        $this->gopay = $g;
        $this->auth = $a;
    }

    public function createPayment(array $rawPayment)
    {
        $payment = $rawPayment + [
            'target' => [
                'type' => 'ACCOUNT',
                'goid' => $this->gopay->getConfig('goid')
            ],
            'lang' => $this->gopay->getConfig('language')
        ];
        return $this->api('', GoPay::JSON, $payment);
    }

    public function getStatus($id)
    {
        return $this->api("/{$id}", GoPay::FORM);
    }

    /** @see refundPaymentEET */
    public function refundPayment($id, $data)
    {
        if (is_array($data)) {
            return $this->refundPaymentEET($id, $data);
        }
        return $this->api("/{$id}/refund", GoPay::FORM, ['amount' => $data]);
    }

    public function refundPaymentEET($id, array $paymentData)
    {
        return $this->api("/{$id}/refund", GoPay::JSON, $paymentData);
    }

    public function createRecurrence($id, array $payment)
    {
        return $this->api("/{$id}/create-recurrence", GoPay::JSON, $payment);
    }

    public function voidRecurrence($id)
    {
        return $this->api("/{$id}/void-recurrence", GoPay::FORM, array());
    }

    public function captureAuthorization($id)
    {
        return $this->api("/{$id}/capture", GoPay::FORM, array());
    }

    public function voidAuthorization($id)
    {
        return $this->api("/{$id}/void-authorization", GoPay::FORM, array());
    }

    /** @return \GoPay\Http\Response */
    private function api($urlPath, $contentType, $data = null)
    {
        $token = $this->auth->authorize();
        if ($token->token) {
            return $this->gopay->call(
                "payments/payment{$urlPath}",
                $contentType,
                "Bearer {$token->token}",
                $data
            );
        }
        return $token->response;
    }

    public function urlToEmbedJs()
    {
        return $this->gopay->buildUrl('gp-gw/js/embed.js');
    }
}
