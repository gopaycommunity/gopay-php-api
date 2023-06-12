<?php

namespace GoPay;

use PHPUnit\Framework\TestCase;

require_once 'TestUtils.php';
require_once 'CreatePaymentTest.php';

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertArrayHasKey;

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

        $response = $this->gopay->createPayment($basePayment);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        assertNotNull($responseBody['id']);
        echo print_r($responseBody, true);
        $st = json_encode($responseBody);

        if ($response->hasSucceed()) {
            print_r("Payment ID: " . $responseBody['id'] . "\n");
            print_r("Payment gwUrl: " . $responseBody['gw_url'] . "\n");
            print_r("Payment state: " . $responseBody['state'] . "\n");
            print_r("PreAuthorization: ");
            echo print_r($responseBody['preauthorization'], true);
        }
    }

    /**
     * returns an error, the preauthorized payment id '3049602803' has been already processed or cancelled
     */
    public function testVoidAuthorization()
    {
        $authorizedPaymentId = 3192064499;

        $response = $this->gopay->voidAuthorization($authorizedPaymentId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey("errors", $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_AUTH_VOID_FAILED');

        echo print_r($responseBody, true);
    }

    /**
     * returns an error, the preauthorized payment id '3049602803' has been already processed or cancelled
     */
    public function testCapturePayment()
    {
        $authorizedPaymentId = 3192064499;

        $response = $this->gopay->captureAuthorization($authorizedPaymentId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey("errors", $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_CAPTURE_DONE');

        echo print_r($responseBody, true);
    }
}
