<?php

namespace GoPay;

require_once 'TestUtils.php';

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertArrayNotHasKey;

/**
 * Class CardTokenTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CardTokenTest extends TestCase
{

    private $gopay;

    protected function setUp(): void
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

    public function testPaymentWithCardTokenRequest()
    {
        $basePayment = self::createBaseCardTokenPayment();
        $basePayment['payer']['request_card_token'] = true;

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


    public function testPaymentWithCardToken()
    {
        $basePayment = self::createBaseCardTokenPayment();
        $basePayment['payer']['allowed_card_token'] = 'X5GMEJIPGhRuIBm/Q5G+D6m0WYnjN70YoLFZhN61UeSu9U0TRrrx0T1Xxvqp2dUEwqBjy62stJFLzkMoRxfeoOfetEnJqotVYntw9BFEp3mbYwkTN7XsAU36MbMkYplwsPmXBeQD9XCYUfjXmn16WQ==';

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

    public function testActiveCardDetails()
    {
        $cardId = 3011475940;

        $response = $this->gopay->getCardDetails($cardId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        assertEquals($responseBody['status'], "ACTIVE");

        echo print_r($responseBody, true);

        if ($response->hasSucceed()) {
            print_r("Card ID: " . $responseBody['card_id'] . "\n");
            print_r("Card status: " . $responseBody['status'] . "\n");
            print_r("Card token: " . $responseBody['card_token'] . "\n");
            print_r("Card fingerprint: " . $responseBody['card_fingerprint'] . "\n");
        }
    }

    public function testDeletedCardDetails()
    {
        $cardId = 3011480505;

        $response = $this->gopay->getCardDetails($cardId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        assertEquals($responseBody['status'], "DELETED");

        echo print_r($responseBody, true);

        if ($response->hasSucceed()) {
            print_r("Card ID: " . $responseBody['card_id'] . "\n");
            print_r("Card status: " . $responseBody['status'] . "\n");
        }
    }

    public function testDeleteCardToken()
    {
        $cardId = 3011480505;

        $response = $this->gopay->deleteCard($cardId);
        assertTrue($response->hasSucceed());
        assertEquals($response->statusCode, 204);
    }
}
