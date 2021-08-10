<?php

namespace GoPay;

use PHPUnit\Framework\TestCase;

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
        $paymentId = 3049604064;

        $response = $this->gopay->refundPayment($paymentId, 2300);

        echo print_r($response->json, true);
        self::assertNotEmpty($response->json);
    }

}
