<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Response\PaymentStatus;
use GoPay\Http\Response;
use GoPay\Payments;
use PHPUnit\Framework\TestCase;

/**
 * Base class of integration tests with assertions of GoPay API responses.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected Payments $gopay;

    protected function setUp(): void
    {
        $this->gopay = $this->createPayments();
    }

    /**
     * Payments client used by the test, override to use another sandbox account.
     */
    protected function createPayments(): Payments
    {
        return TestUtils::setup();
    }

    /**
     * Asserts a successful response with JSON body and returns the decoded body.
     *
     * @return array<mixed>
     */
    protected function assertSucceeded(Response $response, int $statusCode = 200): array
    {
        $this->assertTrue($response->hasSucceed(), "Request failed with status {$response->statusCode}: {$response->rawBody}");
        $this->assertStatusCode($statusCode, $response);
        $this->assertIsArray($response->json, "Response is not JSON: {$response->rawBody}");
        $this->assertArrayNotHasKey('errors', $response->json);
        return $response->json;
    }

    /**
     * Asserts an API error response: status code and name of the first error.
     */
    protected function assertApiError(Response $response, int $statusCode, string $errorName): void
    {
        $this->assertFalse($response->hasSucceed(), "Request was expected to fail: {$response->rawBody}");
        $this->assertStatusCode($statusCode, $response);
        $this->assertIsArray($response->json, "Response is not JSON: {$response->rawBody}");
        $this->assertArrayHasKey('errors', $response->json);
        $this->assertIsArray($response->json['errors']);
        $this->assertNotEmpty($response->json['errors']);
        $error = $response->json['errors'][0];
        $this->assertIsArray($error);
        $this->assertSame($errorName, $error['error_name'] ?? null, "Unexpected error: {$response->rawBody}");
        $this->assertIsInt($error['error_code'] ?? null);
    }

    /**
     * Status code is a numeric string on success and int on error, compare it as int.
     */
    protected function assertStatusCode(int $expected, Response $response): void
    {
        $this->assertIsNumeric($response->statusCode);
        $this->assertSame($expected, (int) $response->statusCode, "Unexpected status code, body: {$response->rawBody}");
    }

    /**
     * Asserts a response of createPayment: new payment in CREATED state matching the sent payment.
     * Target defaults to the goid from the SDK config when the payment does not specify it.
     *
     * @param array<string, mixed> $sentPayment
     *
     * @return array<mixed> decoded response body
     */
    protected function assertPaymentCreated(Response $response, array $sentPayment): array
    {
        $payment = $this->assertSucceeded($response);

        $this->assertIsInt($payment['id'] ?? null);
        $this->assertSame(PaymentStatus::CREATED, $payment['state'] ?? null);
        $this->assertSame($sentPayment['amount'], $payment['amount'] ?? null);
        $this->assertSame($sentPayment['currency'], $payment['currency'] ?? null);
        $this->assertSame($sentPayment['order_number'], $payment['order_number'] ?? null);
        $this->assertIsArray($payment['target'] ?? null);
        $this->assertSame('ACCOUNT', $payment['target']['type'] ?? null);
        $this->assertSame(
            (string) $this->gopay->getGopay()->getConfig('goid'),
            (string) ($payment['target']['goid'] ?? null)
        );
        $this->assertIsArray($sentPayment['payer']);
        $this->assertIsArray($payment['payer'] ?? null);
        $this->assertSame(
            $sentPayment['payer']['allowed_payment_instruments'],
            $payment['payer']['allowed_payment_instruments'] ?? null
        );
        $this->assertIsString($sentPayment['lang']);
        $this->assertIsString($payment['lang'] ?? null);
        $this->assertSame(strtolower($sentPayment['lang']), strtolower($payment['lang']));
        $this->assertIsString($payment['gw_url'] ?? null);
        $this->assertStringStartsWith('https://gw.sandbox.gopay.com/gw-ui/', $payment['gw_url']);

        TestUtils::debug($payment);

        return $payment;
    }
}
