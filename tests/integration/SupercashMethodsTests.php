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
class SupercashMethodsTests extends TestCase
{

    private $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setupSupercash();
    }

    public function testCreateSupercashCoupon()
    {
        $supercashCoupon = [
                'sub_type' => PaymentsSupercash::SUB_TYPE_POSTPAID,
                'custom_id' => 'ID-123457',
                'amount' => 100,
                'order_number' => '1',
                'order_description' => 'supercash_coupon_test',
                'buyer_email' => 'zakaznik@example.com',
                'buyer_phone' => '+420777123456',
                'date_valid_to' => '2018-12-31',
                'notification_url' => 'http://www.example-notify.cz/supercash'
        ];

        $response = $this->gopay->createSupercashCoupon($supercashCoupon);

        assertNotEmpty($response->json);
        echo print_r($response->json, true);
        $st = json_encode($response->json);

        if (strpos($st, 'error_code') === false) {
            print_r("SuperCASH coupon ID: " . $response->json['supercash_coupon_id'] . "\n");
            print_r("SuperCASH number: " . $response->json['supercash_number'] . "\n");
        }
    }

    public function testCreateSupercashCouponBatch()
    {
        $supercashCouponBatch = [
                'batch_completed_notification_url' => 'http://www.notify.cz/super',
                'defaults' => [
                        'sub_type' => PaymentsSupercash::SUB_TYPE_POSTPAID,
                        'amounts' => [300, 400, 500, 600, 700, 800, 900, 1000],
                        'order_description' => 'supercash_coupon_batch_test'
                ],
                'coupons' => [
                        [
                                'buyer_email' => 'zakaznik1@example.com',
                                'custom_id' => 'ID-123457',
                                'buyer_phone' => '+420777666111',
                                'amounts' => [100]
                        ],
                        [
                                'buyer_email' => 'zakaznik2@example.com',
                                'custom_id' => 'ID-123458',
                                'buyer_phone' => '+420777666222',
                                'amounts' => [200]
                        ],
                        [
                                'buyer_email' => 'zakaznik3@example.com',
                                'custom_id' => 'ID-123459',
                                'buyer_phone' => '+420777666333',
                                'amounts' => [300]
                        ]
                ]
        ];

        $response = $this->gopay->createSupercashCouponBatch($supercashCouponBatch);

        assertNotEmpty($response->json['batch_request_id']);

        echo print_r($response->json, true);
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
