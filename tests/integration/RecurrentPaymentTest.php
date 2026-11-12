<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Payment\Recurrence;
use GoPay\Payments;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

use PHPUnit\Framework\TestCase;

/**
 * Class RecurrentPaymentTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RecurrentPaymentTest extends TestCase
{
    private Payments $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    public function testCreateRecurrentPayment(): void
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['recurrence'] = [
            'recurrence_cycle' => Recurrence::WEEKLY,
            'recurrence_period' => '1',
            'recurrence_date_to' => '2100-04-01',
        ];

        $response = $this->gopay->createPayment($basePayment);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        assertNotNull($responseBody['id']);
        TestUtils::debug($response->json);

        if ($response->hasSucceed()) {
            TestUtils::debug('Payment ID: ' . $responseBody['id']);
            TestUtils::debug('Payment gwUrl: ' . $responseBody['gw_url']);
            TestUtils::debug('Payment state: ' . $responseBody['state']);
            TestUtils::debug('Recurrence:');
            TestUtils::debug($responseBody['recurrence']);
        }
    }

    /** Returns an error, as the recurrence for the payment id '3252157272' has been already stopped. */
    public function testVoidRecurrence(): void
    {
        $authorizedPaymentId = 3252157272;

        $response = $this->gopay->voidRecurrence($authorizedPaymentId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey('errors', $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_RECURRENCE_STOPPED');

        TestUtils::debug($responseBody);
    }
}
