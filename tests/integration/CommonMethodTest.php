<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Account\StatementGeneratingFormat;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Definition\Response\PaymentStatus;

/**
 * Class CommonMethodTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CommonMethodTest extends IntegrationTestCase
{
    public function testPaymentStatus(): void
    {
        $paymentId = 3178283550;

        $response = $this->gopay->getStatus($paymentId);

        $payment = $this->assertSucceeded($response);
        $this->assertSame($paymentId, $payment['id'] ?? null);
        $this->assertContains($payment['state'] ?? null, (new \ReflectionClass(PaymentStatus::class))->getConstants());
        $this->assertIsInt($payment['amount'] ?? null);
        $this->assertSame(Currency::CZECH_CROWNS, $payment['currency'] ?? null);
        $this->assertIsArray($payment['target'] ?? null);
        $this->assertSame(TestUtils::GO_ID, (string) ($payment['target']['goid'] ?? null));
        $this->assertIsString($payment['gw_url'] ?? null);

        TestUtils::debug($payment);
    }

    public function testGetPaymentInstruments(): void
    {
        $response = $this->gopay->getPaymentInstruments(TestUtils::GO_ID, Currency::CZECH_CROWNS);

        $body = $this->assertSucceeded($response);
        $this->assertIsArray($body['groups'] ?? null);
        $this->assertNotEmpty($body['groups']);
        $this->assertIsArray($body['enabledPaymentInstruments'] ?? null);
        $instruments = [];
        foreach ($body['enabledPaymentInstruments'] as $instrument) {
            $this->assertIsArray($instrument);
            $this->assertIsString($instrument['paymentInstrument'] ?? null);
            $instruments[] = $instrument['paymentInstrument'];
        }
        $this->assertContains(PaymentInstrument::PAYMENT_CARD, $instruments);
        $this->assertContains(PaymentInstrument::BANK_ACCOUNT, $instruments);

        TestUtils::debug($body);
    }

    public function testGetAccountStatement(): void
    {
        $accountStatement = [
            'date_from' => (new \DateTimeImmutable('first day of last month'))->format('Y-m-d'),
            'date_to' => (new \DateTimeImmutable('last day of last month'))->format('Y-m-d'),
            'goid' => TestUtils::GO_ID,
            'currency' => Currency::CZECH_CROWNS,
            'format' => StatementGeneratingFormat::CSV_A,
        ];

        $response = $this->gopay->getAccountStatement($accountStatement);

        $this->assertTrue($response->hasSucceed(), "Request failed: {$response->rawBody}");
        $this->assertStatusCode(200, $response);
        // Statement is CSV, not JSON: header row of quoted column names separated by semicolons
        $this->assertNull($response->json);
        $this->assertMatchesRegularExpression('/^"[^"]+"(;"[^"]+")+\R/u', $response->rawBody);
    }
}
