<?php

namespace GoPay;

require_once 'TestUtils.php';
require_once 'CreatePaymentTest.php';

use GoPay\Definition\Payment\Recurrence;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertArrayHasKey;

/**
 * Class RecurrentPaymentTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class RecurrentPaymentTest extends TestCase
{

    private $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    public function testCreateRecurrentPayment()
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['recurrence'] = [
            'recurrence_cycle' => Recurrence::WEEKLY,
            'recurrence_period' => "1",
            'recurrence_date_to' => '2100-04-01'
        ];

        $response = $this->gopay->createPayment($basePayment);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        assertNotNull($responseBody['id']);
        echo print_r($response->json, true);

        if ($response->hasSucceed()) {
            print_r("Payment ID: " . $responseBody['id'] . "\n");
            print_r("Payment gwUrl: " . $responseBody['gw_url'] . "\n");
            print_r("Payment state: " . $responseBody['state'] . "\n");
            print_r("Recurrence: ");
            echo print_r($responseBody['recurrence'], true);
        }
    }

    /* Returns an error, as the recurrence for the payment id '3049603544' has been already stopped. */
    public function testVoidRecurrence()
    {
        $authorizedPaymentId = 3049520773;

        $response = $this->gopay->voidRecurrence($authorizedPaymentId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey("errors", $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_RECURRENCE_STOPPED');

        echo print_r($responseBody, true);
    }
}
