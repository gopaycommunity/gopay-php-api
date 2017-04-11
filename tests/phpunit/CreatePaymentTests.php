<?php

namespace GoPay;

require_once 'TestUtils.php';

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Definition\Payment\BankSwiftCode;

/**
 * Class CreatePaymentTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CreatePaymentTests extends \PHPUnit_Framework_TestCase
{

    private $gopay;

    protected function setUp()
    {
        $this->gopay = TestUtils::setup();
    }

    public static function createBasePayment()
    {
        $basePayment = [
                'payer' => [
                        'allowed_payment_instruments' => [PaymentInstrument::BANK_ACCOUNT,
                                PaymentInstrument::PAYMENT_CARD],
                        'allowed_swifts' => [BankSwiftCode::RAIFFEISENBANK, BankSwiftCode::CESKA_SPORITELNA],
                    //'default_swift' => BankSwiftCode::FIO_BANKA,
                    //'default_payment_instrument' => PaymentInstrument::BANK_ACCOUNT,
                        'contact' => [
                                'email' => 'test.test@gopay.cz',
                        ],
                ],
                'order_number' => '9876',
                'amount' => 2300,
                'currency' => Currency::CZECH_CROWNS,
                'order_description' => '9876Description',
                'lang' => Language::CZECH,
                'additional_params' => [
                        array('name' => 'invoicenumber', 'value' => '2015001003')
                ],
                'items' => [
                        ['name' => 'item01', 'amount' => 2300, 'count' => 1],
                ],
                'callback' => [
                        'return_url' => 'https://eshop123.cz/return',
                        'notification_url' => 'https://eshop123.cz/notify'
                ],
        ];

        return $basePayment;
    }

    public function testCreatePayment()
    {
        $basePayment = self::createBasePayment();
        $payment = $this->gopay->createPayment($basePayment);

        echo print_r($payment->json, true);
        $st = json_encode($payment->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $payment->json['id'] . "\n");
            print_r("Payment gwUrl: " . $payment->json['gw_url'] . "\n");
            print_r("Payment state: " . $payment->json['state'] . "\n");
        }
    }

}
