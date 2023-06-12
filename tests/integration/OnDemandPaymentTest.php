<?php

namespace GoPay;

require_once 'TestUtils.php';
require_once 'CreatePaymentTest.php';

use GoPay\Definition\Payment\Recurrence;
use GoPay\Definition\Payment\Currency;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertArrayHasKey;

/**
 * Class OnDemandPaymentTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class OnDemandPaymentTest extends TestCase
{

    private $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    public function testCreateOnDemandPayment()
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['recurrence'] = [
            'recurrence_cycle' => Recurrence::ON_DEMAND,
            'recurrence_date_to' => '2100-04-01'
        ];

        $response = $this->gopay->createPayment($basePayment);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        assertNotNull($responseBody['id']);
        echo print_r($responseBody, true);

        if ($response->hasSucceed()) {
            print_r("OnDemand Payment ID: " . $responseBody['id'] . "\n");
            print_r("OnDemand Payment gwUrl: " . $responseBody['gw_url'] . "\n");
            print_r("OnDemand Payment state: " . $responseBody['state'] . "\n");
            print_r("Recurrence: ");
            echo print_r($responseBody['recurrence'], true);
        }
    }


    public function testCreateNextOnDemandPayment()
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

        $response = $this->gopay->createRecurrence(3049520708, $nextPayment);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey("errors", $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_RECURRENCE_STOPPED');

        echo print_r($responseBody, true);

        if ($response->hasSucceed()) {
            print_r("OnDemand Payment ID: " . $responseBody['id'] . "\n");
            print_r("OnDemand Payment gwUrl: " . $responseBody['gw_url'] . "\n");
            print_r("OnDemand Payment state: " . $responseBody['state'] . "\n");
        }
    }
}
