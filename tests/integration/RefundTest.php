<?php

namespace GoPay\Tests\Integration;

use GoPay\Payments;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;

use PHPUnit\Framework\TestCase;

/**
 * Class RefundTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RefundTest extends TestCase
{
    private Payments $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    /** Returns PAYMENT_REFUND_NOT_SUPPORTED, the payment with id '3178283550' cannot be refunded */
    public function testRefundPayment(): void
    {
        $paymentId = 3178283550;

        $response = $this->gopay->refundPayment($paymentId, 2300);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey('errors', $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_REFUND_NOT_SUPPORTED');

        echo print_r($response->json, true);
    }

    public function testHistoryOfRefunds(): void
    {
        $paymentId = 3178283550;

        $response = $this->gopay->getHistoryRefunds($paymentId);
        $responseBody = $response->json;

        echo print_r($responseBody, true);

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        echo print_r($responseBody, true);
    }
}
