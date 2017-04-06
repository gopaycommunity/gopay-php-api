<?php

namespace GoPay;

require_once 'TestUtils.php';
require_once 'CreatePaymentTests.php';

use GoPay\Definition\Payment\Recurrence;
use GoPay\Definition\Payment\Currency;

/**
 * Class OnDemandPaymentTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class OnDemandPaymentTests extends \PHPUnit_Framework_TestCase
{

    private $gopay;

    protected function setUp()
    {
        $this->gopay = TestUtils::setup();
    }

    public function tCreateOnDemandPayment()
    {
        $basePayment = CreatePaymentTests::createBasePayment();

        $basePayment['recurrence'] = [
                'recurrence_cycle' => Recurrence::ON_DEMAND,
                'recurrence_date_to' => '2018-04-01'
        ];

        $payment = $this->gopay->createPayment($basePayment);

        echo print_r($payment->json, true);
        $st = json_encode($payment->json);

        if (strpos($st, 'error_code') === false) {
            print_r("OnDemand Payment ID: " . $payment->json['id'] . "\n");
            print_r("OnDemand Payment gwUrl: " . $payment->json['gw_url'] . "\n");
            print_r("OnDemand Payment state: " . $payment->json['state'] . "\n");
            print_r("Recurrence: ");
            echo print_r($payment->json['recurrence'], true);
        }
    }

    public function testCreateNextOnDemandPayment()
    {
        $nextPayment = [
                'amount' => 4000,
                'currency' => Currency::CZECH_CROWNS,
                'order_number' => 'OnDemand9876',
                'order_description' => 'OnDemand9876Description',
                'items' => [
                        ['name' => 'item01', 'amount' => 2000, 'count' => 1],
                ],
        ];

        $onDemandPayment = $this->gopay->createRecurrence(3049603895, $nextPayment);

        echo print_r($onDemandPayment->json, true);
        $st = json_encode($onDemandPayment->json);

        if (strpos($st, 'error_code') === false) {
            print_r("OnDemand Payment ID: " . $onDemandPayment->json['id'] . "\n");
            print_r("OnDemand Payment gwUrl: " . $onDemandPayment->json['gw_url'] . "\n");
            print_r("OnDemand Payment state: " . $onDemandPayment->json['state'] . "\n");

        }
    }

}
