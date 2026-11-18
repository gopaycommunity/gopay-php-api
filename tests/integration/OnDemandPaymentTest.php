<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\Recurrence;
use GoPay\Definition\Response\RecurrenceState;

/**
 * Class OnDemandPaymentTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class OnDemandPaymentTest extends IntegrationTestCase
{
    public function testCreateOnDemandPayment(): void
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['recurrence'] = [
            'recurrence_cycle' => Recurrence::ON_DEMAND,
            'recurrence_date_to' => '2100-04-01',
        ];

        $response = $this->gopay->createPayment($basePayment);

        $payment = $this->assertPaymentCreated($response, $basePayment);
        $this->assertSame(
            [
                'recurrence_cycle' => Recurrence::ON_DEMAND,
                'recurrence_date_to' => '2100-04-01',
                'recurrence_state' => RecurrenceState::REQUESTED,
            ],
            $payment['recurrence'] ?? null
        );
    }

    /** Returns an error, as the recurrence for the payment id '3310523762' has been already stopped. */
    public function testCreateNextOnDemandPayment(): void
    {
        $nextPayment = [
            'amount' => 4000,
            'currency' => Currency::CZECH_CROWNS,
            'order_number' => 'OnDemand9876',
            'order_description' => 'OnDemand9876Description',
            'items' => [
                ['name' => 'item01', 'amount' => 2000, 'count' => 1],
            ],
        ];

        $response = $this->gopay->createRecurrence(3310523762, $nextPayment);

        $this->assertApiError($response, 409, 'PAYMENT_RECURRENCE_STOPPED');

        TestUtils::debug($response->json);
    }
}
