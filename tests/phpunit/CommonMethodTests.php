<?php

namespace GoPay;

require_once 'TestUtils.php';

use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Account\StatementGeneratingFormat;

/**
 * Class CommonMethodTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CommonMethodTests extends \PHPUnit_Framework_TestCase
{

    private $gopay;

    protected function setUp()
    {
        $this->gopay = TestUtils::setup();
    }

    public function testPaymentStatus()
    {
        $paymentId = 3049604064;

        $response = $this->gopay->getStatus($paymentId);

        echo print_r($response->json, true);
        $st = json_encode($response->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $response->json['id'] . "\n");
            print_r("Payment gwUrl: " . $response->json['gw_url'] . "\n");
            print_r("Payment state: " . $response->json['state'] . "\n");
        }
    }

    public function tGetPaymentInstruments()
    {
        $paymentInstrumentList = $this->gopay->getPaymentInstruments(TestUtils::GO_ID, Currency::CZECH_CROWNS);

        echo print_r($paymentInstrumentList->json, true);
    }

    public function tGetAccountStatement()
    {
        $accountStatement = [
                'date_from' => '2017-01-01',
                'date_to' => '2017-02-27',
                'goid' => TestUtils::GO_ID,
                'currency' => Currency::CZECH_CROWNS,
                'format' => StatementGeneratingFormat::CSV_A,
        ];

        $statement = $this->gopay->getAccountStatement($accountStatement);

        $st = json_encode($statement->json);

        if (strpos($st, 'error_code') === false) {
            echo $statement;
        } else {
            echo print_r($statement->json, true);
        }
    }

}
