<?php

namespace GoPay;

require_once 'TestUtils.php';
require_once 'CreatePaymentTest.php';

use GoPay\Definition\Payment\Recurrence;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

/**
 * Class RecurrentPaymentTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RecurrentPaymentTest extends TestCase
{

    private $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    public function testCreateRecurrentPayment()
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['recurrence'] = [
            'recurrence_cycle' => Recurrence::WEEKLY,
            'recurrence_period' => "1",
            'recurrence_date_to' => '2100-04-01'
        ];

        $payment = $this->gopay->createPayment($basePayment);

        assertNotEmpty($payment->json);
        assertNotNull($payment->json['id']);

        echo print_r($payment->json, true);
        $st = json_encode($payment->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $payment->json['id'] . "\n");
            print_r("Payment gwUrl: " . $payment->json['gw_url'] . "\n");
            print_r("Payment state: " . $payment->json['state'] . "\n");
            print_r("Recurrence: ");
            echo print_r($payment->json['recurrence'], true);
        }
    }

    /* Returns an error, as the recurrence for the payment id '3049603544' has been already stopped. */
    public function testVoidRecurrence()
    {
        $authorizedPaymentId = 3049603544;

        $response = $this->gopay->voidRecurrence($authorizedPaymentId);
        assertNotEmpty($response->json);
        echo print_r($response->json, true);
    }
}
