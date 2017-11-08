<?php

namespace GoPay;

use GoPay\Definition\RequestMethods;

class Payments
{
    public $gopay;
    public $auth;

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
        return $this->post('payments/payment', GoPay::JSON, $payment);
    }

    public function getStatus($id)
    {
        return $this->get("payments/payment/{$id}", GoPay::FORM);
    }

    /** @see refundPaymentEET */
    public function refundPayment($id, $data)
    {
        if (is_array($data)) {
            return $this->refundPaymentEET($id, $data);
        }
        return $this->post("payments/payment/{$id}/refund", GoPay::FORM, ['amount' => $data]);
    }

    public function refundPaymentEET($id, array $paymentData)
    {
        return $this->post("payments/payment/{$id}/refund", GoPay::JSON, $paymentData);
    }

    public function createRecurrence($id, array $payment)
    {
        return $this->post("payments/payment/{$id}/create-recurrence", GoPay::JSON, $payment);
    }

    public function voidRecurrence($id)
    {
        return $this->post("payments/payment/{$id}/void-recurrence", GoPay::FORM, array());
    }

    public function captureAuthorization($id)
    {
        return $this->post("payments/payment/{$id}/capture", GoPay::FORM, array());
    }

    public function captureAuthorizationPartial($id, array $capturePayment)
    {
        return $this->post("payments/payment/{$id}/capture", GoPay::JSON, $capturePayment);
    }

    public function voidAuthorization($id)
    {
        return $this->post("payments/payment/{$id}/void-authorization", GoPay::FORM, array());
    }

    public function getPaymentInstruments($goid, $currency)
    {
        return $this->get("eshops/eshop/{$goid}/payment-instruments/{$currency}", null);
    }

    public function getAccountStatement(array $accountStatement)
    {
        return $this->post("accounts/account-statement", GoPay::JSON, $accountStatement);
    }

    public function getEETReceiptByPaymentId($paymentId)
    {
        return $this->get("payments/payment/{$paymentId}/eet-receipts", GoPay::JSON);
    }

    public function findEETReceiptsByFilter(array $filter)
    {
        return $this->post("eet-receipts", GoPay::JSON, $filter);
    }


    // prepsat metodu api na metody GET a POST, a metode call se bude predavat parametr METHOD

    /** @return \GoPay\Http\Response */
    public function get($urlPath, $contentType, $data = null)
    {
        $token = $this->auth->authorize();
        if ($token->token) {
            return $this->gopay->call(
                $urlPath,
                $contentType,
                "Bearer {$token->token}",
                RequestMethods::GET,
                $data
            );
        }
        return $token->response;
    }

    /** @return \GoPay\Http\Response */
    public function post($urlPath, $contentType, $data = null)
    {
        $token = $this->auth->authorize();
        if ($token->token) {
            return $this->gopay->call(
                $urlPath,
                $contentType,
                "Bearer {$token->token}",
                RequestMethods::POST,
                $data
            );
        }
        return $token->response;
    }

    public function urlToEmbedJs()
    {
        return $this->gopay->buildUrl('gp-gw/js/embed.js');
    }

    public function getGopay()
    {
        return $this->gopay;
    }

    public function getAuth()
    {
        return $this->auth;
    }
}
