<?php

namespace GoPay;

require_once 'TestUtils.php';

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Definition\Payment\BankSwiftCode;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertArrayHasKey;

/**
 * Class CreatePaymentTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CreatePaymentTest extends TestCase
{

    private $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    public static function createBasePayment()
    {
        $basePayment = [
            'payer' => [
                'allowed_payment_instruments' => [
                    PaymentInstrument::BANK_ACCOUNT,
                    PaymentInstrument::PAYMENT_CARD
                ],
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

        $response = $this->gopay->createPayment($basePayment);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);
        assertNotNull($responseBody['id']);

        echo print_r($responseBody, true);

        if ($response->hasSucceed()) {
            print_r("Payment ID: " . $responseBody['id'] . "\n");
            print_r("Payment gwUrl: " . $responseBody['gw_url'] . "\n");
            print_r("Payment state: " . $responseBody['state'] . "\n");
        }
    }
}
