<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Language;
use GoPay\Definition\Payment\BankSwiftCode;
use GoPay\Definition\Payment\BnplType;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Payments;

use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

use PHPUnit\Framework\TestCase;

/**
 * Class CreatePaymentTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CreatePaymentTest extends TestCase
{
    private Payments $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    /**
     * @return array<string, mixed>
     */
    public static function createBasePayment(): array
    {
        $basePayment = [
            'payer' => [
                'allowed_payment_instruments' => [
                    PaymentInstrument::BANK_ACCOUNT,
                    PaymentInstrument::PAYMENT_CARD,
                    #       PaymentInstrument::TWISTO,
                    #       PaymentInstrument::SKIPPAY
                ],
                #   'allowed_bnpl_types' => [
                #           BnplType::DEFERRED_PAYMENT,
                #           BnplType::PAY_IN_THREE
                #   ],
                #   'default_bnpl_type' => BnplType::DEFERRED_PAYMENT,
                #   'default_payment_instrument' => PaymentInstrument::TWISTO
                'allowed_swifts' => [BankSwiftCode::RAIFFEISENBANK, BankSwiftCode::CESKA_SPORITELNA],
                #   'default_swift' => BankSwiftCode::FIO_BANKA,
                #   'default_payment_instrument' => PaymentInstrument::BANK_ACCOUNT,
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
                ['name' => 'invoicenumber', 'value' => '2015001003'],
            ],
            'items' => [
                ['name' => 'item01', 'amount' => 2300, 'count' => 1],
            ],
            'callback' => [
                'return_url' => 'https://eshop123.cz/return',
                'notification_url' => 'https://eshop123.cz/notify',
            ],
        ];

        return $basePayment;
    }

    public function testCreatePayment(): void
    {
        $basePayment = self::createBasePayment();

        $response = $this->gopay->createPayment($basePayment);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);
        assertNotNull($responseBody['id']);

        TestUtils::debug($responseBody);

        if ($response->hasSucceed()) {
            TestUtils::debug('Payment ID: ' . $responseBody['id']);
            TestUtils::debug('Payment gwUrl: ' . $responseBody['gw_url']);
            TestUtils::debug('Payment state: ' . $responseBody['state']);
        }
    }
}
