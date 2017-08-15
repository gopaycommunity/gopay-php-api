<?php

namespace GoPay;

require_once 'TestUtils.php';

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;

/**
 * Class CardTokenTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CardTokenTests extends \PHPUnit_Framework_TestCase
{

    private $gopay;

    protected function setUp()
    {
        $this->gopay = TestUtils::setup();
    }

    public static function createBaseCardTokenPayment()
    {
        $basePayment = [
                'payer' => [
                        'allowed_payment_instruments' => [PaymentInstrument::PAYMENT_CARD],
                        'default_payment_instrument' => PaymentInstrument::PAYMENT_CARD,
                        'contact' => [
                                'first_name' => 'Jarda',
                                'last_name' => 'Sokol',
                                'email' => 'test-sokol25@test.cz',
                        ],
                        'allowed_card_token' => 'VUHweq2TUuQpgU6UaD4c+123xzUwTBXiZK7jHhW7rhSbUb07XcG69Q0cwTxTYvBG3qyym3sJ5zphQS4vL0kEHvvinxXYMqkZtx4rBA9mtZj9JSpy4cIHkXnH3gR+i6CoQ4M+zI2EXGJ+TQ==',
//                        'verify_pin' => '',
                ],
                'order_number' => '9876',
                'amount' => 2000,
                'currency' => Currency::CZECH_CROWNS,
                'order_description' => '9876Description',
                'lang' => Language::CZECH,
                'additional_params' => [
                        array('name' => 'invoicenumber', 'value' => '2015001003')
                ],
                'items' => [
                        ['name' => 'item01', 'amount' => 2000, 'count' => 1],
                ],
                'callback' => [
                        'return_url' => 'https://eshop123.cz/return',
                        'notification_url' => 'https://eshop123.cz/notify'
                ],
        ];

        return $basePayment;
    }

    /*
     * All fields on gateway are pre-filled with using card-token.
     * */
    public function testPaymentWithCardToken()
    {
        $basePayment = self::createBaseCardTokenPayment();
        $payment = $this->gopay->createPayment($basePayment);

        echo print_r($payment->json, true);
        $st = json_encode($payment->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $payment->json['id'] . "\n");
            print_r("Payment gwUrl: " . $payment->json['gw_url'] . "\n");
            print_r("Payment state: " . $payment->json['state'] . "\n");
        }
    }

    /*
     * After payment completion the used card-token can be found in created payment.
     * */
    public function tCardTokenPaymentStatus()
    {
        $paymentId = 3052266581;
        $response = $this->gopay->getStatus($paymentId);

        echo print_r($response->json, true);
        $st = json_encode($response->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $response->json['id'] . "\n");
            print_r("Payment gwUrl: " . $response->json['gw_url'] . "\n");
            print_r("Payment state: " . $response->json['state'] . "\n");
            print_r("PayerCard - card token: " . $response->json['payer']['payment_card']['card_token'] . "\n");
            print_r("Payer 3DS Result: " . $response->json['payer']['payment_card']['3ds_result'] . "\n");
        }
    }

}
