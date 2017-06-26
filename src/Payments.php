<?php

namespace GoPay;

class Payments
{
    protected $gopay;
    protected $auth;

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
        return $this->api('payments/payment', GoPay::JSON, $payment);
    }

    public function getStatus($id)
    {
        return $this->api("payments/payment/{$id}", GoPay::FORM);
    }

    /** @see refundPaymentEET */
    public function refundPayment($id, $data)
    {
        if (is_array($data)) {
            return $this->refundPaymentEET($id, $data);
        }
        return $this->api("payments/payment/{$id}/refund", GoPay::FORM, ['amount' => $data]);
    }

    public function refundPaymentEET($id, array $paymentData)
    {
        return $this->api("payments/payment/{$id}/refund", GoPay::JSON, $paymentData);
    }

    public function createRecurrence($id, array $payment)
    {
        return $this->api("payments/payment/{$id}/create-recurrence", GoPay::JSON, $payment);
    }

    public function voidRecurrence($id)
    {
        return $this->api("payments/payment/{$id}/void-recurrence", GoPay::FORM, array());
    }

    public function captureAuthorization($id)
    {
        return $this->api("payments/payment/{$id}/capture", GoPay::FORM, array());
    }

    public function voidAuthorization($id)
    {
        return $this->api("payments/payment/{$id}/void-authorization", GoPay::FORM, array());
    }

    public function getPaymentInstruments($goid, $currency)
    {
        return $this->api("eshops/eshop/{$goid}/payment-instruments/{$currency}", null);
    }

    public function getAccountStatement(array $accountStatement)
    {
        return $this->api("accounts/account-statement", GoPay::JSON, $accountStatement);
    }

    public function getEETReceiptByPaymentId($paymentId)
    {
        return $this->api("payments/payment/{$paymentId}/eet-receipts", GoPay::JSON);
    }

    public function findEETReceiptsByFilter(array $filter)
    {
        return $this->api("eet-receipts", GoPay::JSON, $filter);
    }


    /** @return \GoPay\Http\Response */
    protected function api($urlPath, $contentType, $data = null)
    {
        $token = $this->auth->authorize();
        if ($token->token) {
            return $this->gopay->call(
                $urlPath,
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
