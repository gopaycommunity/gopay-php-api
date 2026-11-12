<?php

namespace GoPay;

require_once 'TestUtils.php';

use GoPay\Definition\Account\StatementGeneratingFormat;
use GoPay\Definition\Payment\Currency;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;

use PHPUnit\Framework\TestCase;

/**
 * Class CommonMethodTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class CommonMethodTest extends TestCase
{
    private Payments $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    public function testPaymentStatus(): void
    {
        $paymentId = 3178283550;

        $response = $this->gopay->getStatus($paymentId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        assertNotNull($responseBody['id']);
        echo print_r($responseBody, true);

        if ($response->hasSucceed()) {
            print_r('Payment ID: ' . $responseBody['id'] . "\n");
            print_r('Payment gwUrl: ' . $responseBody['gw_url'] . "\n");
            print_r('Payment state: ' . $responseBody['state'] . "\n");
        }
    }

    public function testGetPaymentInstruments(): void
    {
        $response = $this->gopay->getPaymentInstruments(TestUtils::GO_ID, Currency::CZECH_CROWNS);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);
        assertArrayHasKey('enabledPaymentInstruments', $responseBody);

        echo print_r($responseBody, true);
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

        assertTrue($response->hasSucceed());
        assertEquals(200, $response->statusCode);
        assertNotEmpty($response->rawBody);
    }
}
