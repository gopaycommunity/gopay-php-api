<?php

namespace GoPay;

require_once 'TestUtils.php';
require_once 'CreatePaymentTests.php';

/**
 * Class PreAuthorizationTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class PreAuthorizationTests extends \PHPUnit_Framework_TestCase
{

    private $gopay;

    protected function setUp()
    {
        $this->gopay = TestUtils::setup();
    }

    public function tCreatePreAuthorizedPayment()
    {
        $basePayment = CreatePaymentTests::createBasePayment();

        $basePayment['preauthorization'] = true;

        $payment = $this->gopay->createPayment($basePayment);

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

    public function tVoidAuthorization()
    {
        $authorizedPaymentId = 3049602803;

        $response = $this->gopay->voidAuthorization($authorizedPaymentId);

        echo print_r($response->json, true);
    }

    public function testCapturePayment()
    {
        $authorizedPaymentId = 3049603050;

        $response = $this->gopay->captureAuthorization($authorizedPaymentId);

        echo print_r($response->json, true);

    }

}
