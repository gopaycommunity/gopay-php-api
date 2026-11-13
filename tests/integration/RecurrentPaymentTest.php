<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Payment\Recurrence;
use GoPay\Definition\Response\RecurrenceState;

/**
 * Class RecurrentPaymentTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RecurrentPaymentTest extends IntegrationTestCase
{
    public function testCreateRecurrentPayment(): void
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['recurrence'] = [
            'recurrence_cycle' => Recurrence::WEEKLY,
            'recurrence_period' => '1',
            'recurrence_date_to' => '2100-04-01',
        ];

        $response = $this->gopay->createPayment($basePayment);

        $payment = $this->assertPaymentCreated($response, $basePayment);
        $this->assertSame(
            [
                'recurrence_cycle' => Recurrence::WEEKLY,
                'recurrence_period' => 1,
                'recurrence_date_to' => '2100-04-01',
                'recurrence_state' => RecurrenceState::REQUESTED,
            ],
            $payment['recurrence'] ?? null
        );
    }

    /** Returns an error, as the recurrence for the payment id '3252157272' has been already stopped. */
    public function testVoidRecurrence(): void
    {
        $authorizedPaymentId = 3252157272;

        $response = $this->gopay->voidRecurrence($authorizedPaymentId);

        $this->assertApiError($response, 409, 'PAYMENT_RECURRENCE_STOPPED');

        TestUtils::debug($response->json);
    }
}
