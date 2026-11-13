<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Payment\Currency;

/**
 * Class RefundTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RefundTest extends IntegrationTestCase
{
    /** Returns PAYMENT_REFUND_NOT_SUPPORTED, the payment with id '3178283550' cannot be refunded */
    public function testRefundPayment(): void
    {
        $paymentId = 3178283550;

        $response = $this->gopay->refundPayment($paymentId, 2300);

        $this->assertApiError($response, 409, 'PAYMENT_REFUND_NOT_SUPPORTED');

        TestUtils::debug($response->json);
    }

    public function testHistoryOfRefunds(): void
    {
        $paymentId = 3178283550;

        $response = $this->gopay->getHistoryRefunds($paymentId);

        $refunds = $this->assertSucceeded($response);
        $this->assertNotEmpty($refunds);
        $this->assertTrue(array_is_list($refunds), 'History of refunds must be a list');
        foreach ($refunds as $refund) {
            $this->assertIsArray($refund);
            $this->assertIsInt($refund['id'] ?? null);
            $this->assertIsString($refund['state'] ?? null);
            $this->assertIsInt($refund['amount'] ?? null);
            $this->assertSame(Currency::CZECH_CROWNS, $refund['currency'] ?? null);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', (string) ($refund['date_requested'] ?? ''));
        }

        TestUtils::debug($refunds);
    }
}
