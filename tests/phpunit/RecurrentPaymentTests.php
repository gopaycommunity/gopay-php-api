<?php

namespace GoPay;

require_once 'TestUtils.php';
require_once 'CreatePaymentTests.php';

use GoPay\Definition\Payment\Recurrence;

/**
 * Class RecurrentPaymentTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RecurrentPaymentTests extends \PHPUnit_Framework_TestCase
{

    private $gopay;

    protected function setUp()
    {
        $this->gopay = TestUtils::setup();
    }

    public function tCreateRecurrentPayment()
    {
        $basePayment = CreatePaymentTests::createBasePayment();

        $basePayment['recurrence'] = [
            'recurrence_cycle' => Recurrence::WEEKLY,
            'recurrence_period' => "1",
            'recurrence_date_to' => '2018-04-01'
        ];

        $payment = $this->gopay->createPayment($basePayment);

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

    public function testVoidRecurrence()
    {
        $authorizedPaymentId = 3049603544;

        $response = $this->gopay->voidRecurrence($authorizedPaymentId);

        echo print_r($response->json, true);
    }

}
