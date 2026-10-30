<?php

namespace GoPay;

use GoPay\Definition\RequestMethods;
use GoPay\Http\Response;

class Payments
{
    /** @var GoPay */
    public $gopay;
    /** @var Auth */
    public $auth;

    public function __construct(GoPay $g, Auth $a)
    {
        $this->gopay = $g;
        $this->auth = $a;
    }

    /**
     * @param array<string, mixed> $rawPayment
     * @return Response
     */
    public function createPayment(array $rawPayment)
    {
        $payment = $rawPayment + [
            'target' => [
                'type' => 'ACCOUNT',
                'goid' => $this->gopay->getConfig('goid')
            ],
            'lang' => $this->gopay->getConfig('language')
        ];
        return $this->post('/payments/payment', GoPay::JSON, $payment);
    }

    /**
     * @param int|string $id
     * @return Response
     */
    public function getStatus($id)
    {
        return $this->get("/payments/payment/{$id}");
    }

    /**
     * Returns QR payment info for a given payment.
     *
     * @param int|string $id     Payment ID
     * @param string|null $format Image format of the QR code: 'png' (default) or 'svg'
     * @return Response
     */
    public function getQrPayment($id, ?string $format = null)
    {
        $urlPath = "/payments/payment/{$id}/qr-payment";
        if ($format !== null) {
            $urlPath .= '?format=' . urlencode($format);
        }
        return $this->get($urlPath);
    }

    /**
     * @see refundPaymentEET
     * @param int|string $id
     * @param int|string|array<string, mixed> $data amount to refund, array delegates to refundPaymentEET
     * @return Response
     */
    public function refundPayment($id, $data)
    {
        if (is_array($data)) {
            return $this->refundPaymentEET($id, $data);
        }
        return $this->post("/payments/payment/{$id}/refund", GoPay::FORM, ['amount' => $data]);
    }


    /**
     * @param int|string $id
     * @param array<string, mixed> $payment
     * @return Response
     */
    public function createRecurrence($id, array $payment)
    {
        return $this->post("/payments/payment/{$id}/create-recurrence", GoPay::JSON, $payment);
    }

    /**
     * @param int|string $id
     * @return Response
     */
    public function voidRecurrence($id)
    {
        return $this->post("/payments/payment/{$id}/void-recurrence", GoPay::FORM, array());
    }

    /**
     * @param int|string $id
     * @return Response
     */
    public function captureAuthorization($id)
    {
        return $this->post("/payments/payment/{$id}/capture", GoPay::FORM, array());
    }

    /**
     * @param int|string $id
     * @param array<string, mixed> $capturePayment
     * @return Response
     */
    public function captureAuthorizationPartial($id, array $capturePayment)
    {
        return $this->post("/payments/payment/{$id}/capture", GoPay::JSON, $capturePayment);
    }

    /**
     * @param int|string $id
     * @return Response
     */
    public function voidAuthorization($id)
    {
        return $this->post("/payments/payment/{$id}/void-authorization", GoPay::FORM, array());
    }

    /**
     * @param int|string $cardId
     * @return Response
     */
    public function getCardDetails($cardId)
    {
        return $this->get("/payments/cards/{$cardId}");
    }

    /**
     * @param int|string $cardId
     * @return Response
     */
    public function deleteCard($cardId)
    {
        return $this->delete("/payments/cards/{$cardId}");
    }

    /**
     * @param int|string $id
     * @return Response
     */
    public function getHistoryRefunds($id)
    {
        return $this->get("/payments/payment/{$id}/refunds");
    }

    /**
     * @param int|string $goid
     * @param string $currency one of Currency constants
     * @return Response
     */
    public function getPaymentInstruments($goid, $currency)
    {
        return $this->get("/eshops/eshop/{$goid}/payment-instruments/{$currency}");
    }
    /**
     * @param int|string $goid
     * @return Response
     */
    public function getPaymentInstrumentsAll($goid)
    {
        return $this->get("/eshops/eshop/{$goid}/payment-instruments");
    }

    /**
     * @param array<string, mixed> $accountStatement
     * @return Response
     */
    public function getAccountStatement(array $accountStatement)
    {
        return $this->post("/accounts/account-statement", GoPay::JSON, $accountStatement);
    }

    /**
     * @param int|string $id
     * @param array<string, mixed> $paymentData
     * @return Response
     */
    public function refundPaymentEET($id, array $paymentData)
    {
        return $this->post("/payments/payment/{$id}/refund", GoPay::JSON, $paymentData);
    }
    /**
     * @param int|string $paymentId
     * @return Response
     */
    public function getEETReceiptByPaymentId($paymentId)
    {
        return $this->get("/payments/payment/{$paymentId}/eet-receipts");
    }

    /**
     * @param array<string, mixed> $filter
     * @return Response
     */
    public function findEETReceiptsByFilter(array $filter)
    {
        return $this->post("/eet-receipts", GoPay::JSON, $filter);
    }


    // prepsat metodu api na metody GET a POST, a metode call se bude predavat parametr METHOD

    /**
     * If the access token cannot be obtained, the OAuth error response is returned instead.
     *
     * @param string $urlPath
     * @return Response
     */
    public function get($urlPath)
    {
        $token = $this->auth->authorize();
        if ($token->token) {
            return $this->gopay->call(
                $urlPath,
                "Bearer {$token->token}",
                RequestMethods::GET
            );
        }
        // OAuth2 always sets response; @return stays Response to keep the public API type
        return $token->response; // @phpstan-ignore return.type
    }

    /**
     * If the access token cannot be obtained, the OAuth error response is returned instead.
     *
     * @param string $urlPath
     * @param string $contentType GoPay::JSON or GoPay::FORM
     * @param array<mixed>|null $data
     * @return Response
     */
    public function post($urlPath, $contentType, $data = null)
    {
        $token = $this->auth->authorize();
        if ($token->token) {
            return $this->gopay->call(
                $urlPath,
                "Bearer {$token->token}",
                RequestMethods::POST,
                $contentType,
                $data
            );
        }
        // OAuth2 always sets response; @return stays Response to keep the public API type
        return $token->response; // @phpstan-ignore return.type
    }

    /**
     * If the access token cannot be obtained, the OAuth error response is returned instead.
     *
     * @param string $urlPath
     * @return Response
     */
    public function delete($urlPath)
    {
        $token = $this->auth->authorize();
        if ($token->token) {
            return $this->gopay->call(
                $urlPath,
                "Bearer {$token->token}",
                RequestMethods::DELETE
            );
        }
        // OAuth2 always sets response; @return stays Response to keep the public API type
        return $token->response; // @phpstan-ignore return.type
    }

    /**
     * @return string
     */
    public function urlToEmbedJs()
    {
        return $this->gopay->buildEmbedUrl();
    }

    /**
     * @return GoPay
     */
    public function getGopay()
    {
        return $this->gopay;
    }

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->auth;
    }
}
