<?php

namespace GoPay;

use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

require_once 'TestUtils.php';

/**
 * Class RefundTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RefundTest extends TestCase
{

    private $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    /** This test will always return an error, as the payment with id '3049604064' has been already refunded */
    public function testRefundPayment()
    {
        $paymentId = 3178283550;

        $response = $this->gopay->refundPayment($paymentId, 2300);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey('errors', $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_WRONG_STATE');

        echo print_r($response->json, true);
    }
}
