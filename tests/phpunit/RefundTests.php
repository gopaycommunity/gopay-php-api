<?php

namespace GoPay;

require_once 'TestUtils.php';

/**
 * Class RefundTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RefundTests extends \PHPUnit_Framework_TestCase
{

    private $gopay;

    protected function setUp()
    {
        $this->gopay = TestUtils::setup();
    }

    public function testRefundPayment()
    {
        $paymentId = 3049604064;

        $response = $this->gopay->refundPayment($paymentId, 2300);

        echo print_r($response->json, true);
    }

}
