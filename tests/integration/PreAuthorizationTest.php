<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Response\PreAuthState;

/**
 * Class PreAuthorizationTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class PreAuthorizationTest extends IntegrationTestCase
{
    public function testCreatePreAuthorizedPayment(): void
    {
        $basePayment = CreatePaymentTest::createBasePayment();

        $basePayment['preauthorization'] = true;

        $response = $this->gopay->createPayment($basePayment);

        $payment = $this->assertPaymentCreated($response, $basePayment);
        $this->assertSame(
            ['requested' => true, 'state' => PreAuthState::REQUESTED],
            $payment['preauthorization'] ?? null
        );
    }

    /**
     * returns an error, the preauthorized payment id '3192064499' has been already processed or cancelled
     */
    public function testVoidAuthorization(): void
    {
        $authorizedPaymentId = 3192064499;

        $response = $this->gopay->voidAuthorization($authorizedPaymentId);

        $this->assertApiError($response, 409, 'PAYMENT_AUTH_VOID_FAILED');

        TestUtils::debug($response->json);
    }

    /**
     * returns an error, the preauthorized payment id '3192064499' has been already processed or cancelled
     */
    public function testCapturePayment(): void
    {
        $authorizedPaymentId = 3192064499;

        $response = $this->gopay->captureAuthorization($authorizedPaymentId);

        $this->assertApiError($response, 409, 'PAYMENT_CAPTURE_DONE');

        TestUtils::debug($response->json);
    }
}
