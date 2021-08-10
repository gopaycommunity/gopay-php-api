<?php

namespace GoPay;

use PHPUnit\Framework\TestCase;

require_once 'TestUtils.php';
require_once 'CreatePaymentTest.php';

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

/**
 * Class PreAuthorizationTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class PreAuthorizationTest extends TestCase
{

    private $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    public function testCreatePreAuthorizedPayment()
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['preauthorization'] = true;

        $payment = $this->gopay->createPayment($basePayment);

        assertNotEmpty($payment->json);
        assertNotNull($payment->json['id']);
        echo print_r($payment->json, true);
        $st = json_encode($payment->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $payment->json['id'] . "\n");
            print_r("Payment gwUrl: " . $payment->json['gw_url'] . "\n");
            print_r("Payment state: " . $payment->json['state'] . "\n");
            print_r("PreAuthorization: ");
            echo print_r($payment->json['preauthorization'], true);
        }
    }

    /**
     * returns an error, the preauthorized payment id '3049602803' has been already processed or cancelled
     */
    public function testVoidAuthorization()
    {
        $authorizedPaymentId = 3049602803;

        $response = $this->gopay->voidAuthorization($authorizedPaymentId);
        assertNotEmpty($response->json);

        echo print_r($response->json, true);
    }

    /**
     * returns an error, the preauthorized payment id '3049602803' has been already processed or cancelled
     */
    public function testCapturePayment()
    {
        $authorizedPaymentId = 3049603050;

        $response = $this->gopay->captureAuthorization($authorizedPaymentId);
        assertNotEmpty($response->json);

        echo print_r($response->json, true);

    }

}
