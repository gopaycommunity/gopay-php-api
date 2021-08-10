<?php

namespace GoPay;

require_once 'TestUtils.php';

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertNotEmpty;
/**
 * Class SupercashMethodsTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 *
 */
class SupercashMethodsTest extends TestCase
{

    private $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setupSupercash();
    }

    public function testGetSupercashCouponBatchStatus()
    {
        $batchId = 961667719;

        $response = $this->gopay->getSupercashCouponBatchStatus($batchId);
        assertNotEmpty($response->json);
        echo print_r($response->json, true);
    }

    public function testGetSupercashCouponBatch()
    {
        $batchId = 961667719;

        $response = $this->gopay->getSupercashCouponBatch($batchId);
        assertNotEmpty($response->json);
        echo print_r($response->json, true);
    }

    public function testFindSupercashCoupons()
    {
        $paymentSessionId = [3050857992, 3050858018];

        $response = $this->gopay->findSupercashCoupons($paymentSessionId);
        assertNotEmpty($response->json);
        echo print_r($response->json, true);
    }

    public function testGetSupercashCoupon()
    {
        $couponId = 100154175;

        $response = $this->gopay->getSupercashCoupon($couponId);
        assertNotEmpty($response->json);
        echo print_r($response->json, true);
    }

}
