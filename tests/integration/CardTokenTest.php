<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;

/**
 * Class CardTokenTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CardTokenTest extends IntegrationTestCase
{
    /**
     * @return array<string, mixed>
     */
    public static function createBaseCardTokenPayment(): array
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
                ['name' => 'invoicenumber', 'value' => '2015001003'],
            ],
            'items' => [
                ['name' => 'item01', 'amount' => 2000, 'count' => 1],
            ],
            'callback' => [
                'return_url' => 'https://eshop123.cz/return',
                'notification_url' => 'https://eshop123.cz/notify',
            ],
        ];

        return $basePayment;
    }

    public function testPaymentWithCardTokenRequest(): void
    {
        $basePayment = self::createBaseCardTokenPayment();
        $basePayment['payer']['request_card_token'] = true;

        $response = $this->gopay->createPayment($basePayment);

        $payment = $this->assertPaymentCreated($response, $basePayment);
        $this->assertIsArray($payment['payer'] ?? null);
        $this->assertSame(PaymentInstrument::PAYMENT_CARD, $payment['payer']['default_payment_instrument'] ?? null);
    }


    public function testPaymentWithCardToken(): void
    {
        $this->markTestSkipped('The hardcoded card token is no longer valid (gateway returns errors in response).');

        // @phpstan-ignore deadCode.unreachable (test is skipped, body kept for re-enabling)
        $basePayment = self::createBaseCardTokenPayment();
        $basePayment['payer']['allowed_card_token'] = 'X5GMEJIPGhRuIBm/Q5G+D6m0WYnjN70YoLFZhN61UeSu9U0TRrrx0T1Xxvqp2dUEwqBjy62stJFLzkMoRxfeoOfetEnJqotVYntw9BFEp3mbYwkTN7XsAU36MbMkYplwsPmXBeQD9XCYUfjXmn16WQ==';

        $response = $this->gopay->createPayment($basePayment);

        $this->assertPaymentCreated($response, $basePayment);
    }

    public function testActiveCardDetails(): void
    {
        $this->markTestSkipped('The card state in the sandbox has changed since the test was written, card 3011475940 is DELETED instead of ACTIVE.');

        // @phpstan-ignore deadCode.unreachable (test is skipped, body kept for re-enabling)
        $cardId = 3011475940;

        $response = $this->gopay->getCardDetails($cardId);

        $card = $this->assertSucceeded($response);
        $this->assertSame($cardId, $card['card_id'] ?? null);
        $this->assertSame('ACTIVE', $card['status'] ?? null);
        $this->assertIsString($card['card_token'] ?? null);
        $this->assertIsString($card['card_fingerprint'] ?? null);

        TestUtils::debug($card);
    }

    public function testDeletedCardDetails(): void
    {
        $cardId = 3011480505;

        $response = $this->gopay->getCardDetails($cardId);

        $card = $this->assertSucceeded($response);
        $this->assertSame(['card_id' => $cardId, 'status' => 'DELETED'], $card);

        TestUtils::debug($card);
    }

    public function testDeleteCardToken(): void
    {
        $cardId = 3011480505;

        $response = $this->gopay->deleteCard($cardId);

        // Deleting an already deleted card succeeds, 204 has no body
        $this->assertTrue($response->hasSucceed(), "Request failed: {$response->rawBody}");
        $this->assertStatusCode(204, $response);
        $this->assertSame('', $response->rawBody);
    }
}
