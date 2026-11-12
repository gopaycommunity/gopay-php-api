<?php

namespace GoPay\Tests\Integration;

use GoPay\Payments;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

use PHPUnit\Framework\TestCase;

/**
 * Class PreAuthorizationTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class PreAuthorizationTest extends TestCase
{
    private Payments $gopay;

    protected function setUp(): void
    {
        $this->gopay = TestUtils::setup();
    }

    public function testCreatePreAuthorizedPayment(): void
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['preauthorization'] = true;

        $response = $this->gopay->createPayment($basePayment);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);
        assertArrayNotHasKey('errors', $responseBody);

        assertNotNull($responseBody['id']);
        TestUtils::debug($responseBody);

        if ($response->hasSucceed()) {
            TestUtils::debug('Payment ID: ' . $responseBody['id']);
            TestUtils::debug('Payment gwUrl: ' . $responseBody['gw_url']);
            TestUtils::debug('Payment state: ' . $responseBody['state']);
            TestUtils::debug('PreAuthorization:');
            TestUtils::debug($responseBody['preauthorization']);
        }
    }

    /**
     * returns an error, the preauthorized payment id '3192064499' has been already processed or cancelled
     */
    public function testVoidAuthorization(): void
    {
        $authorizedPaymentId = 3192064499;

        $response = $this->gopay->voidAuthorization($authorizedPaymentId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey('errors', $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_AUTH_VOID_FAILED');

        TestUtils::debug($responseBody);
    }

    /**
     * returns an error, the preauthorized payment id '3192064499' has been already processed or cancelled
     */
    public function testCapturePayment(): void
    {
        $authorizedPaymentId = 3192064499;

        $response = $this->gopay->captureAuthorization($authorizedPaymentId);
        $responseBody = $response->json;

        assertNotEmpty($responseBody);

        assertArrayHasKey('errors', $responseBody);
        $message = $responseBody['errors'][0]['error_name'];
        assertEquals($message, 'PAYMENT_CAPTURE_DONE');

        TestUtils::debug($responseBody);
    }
}
