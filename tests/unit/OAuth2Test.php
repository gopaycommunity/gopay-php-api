<?php

namespace GoPay\Tests\Unit;

use GoPay\Definition\RequestMethods;
use GoPay\Definition\TokenScope;
use GoPay\GoPay;
use GoPay\OAuth2;
use PHPUnit\Framework\TestCase;

class OAuth2Test extends TestCase
{
    private function oauth(RecordingBrowser $browser): OAuth2
    {
        return new OAuth2(new GoPay(
            [
                'clientId' => 'client-id',
                'clientSecret' => 'client-secret',
                'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
                'scope' => TokenScope::CREATE_PAYMENT,
                'language' => 'EN',
            ],
            $browser
        ));
    }

    public function testAuthorizeRequestsTokenWithClientCredentials(): void
    {
        $browser = new RecordingBrowser(RecordingBrowser::tokenResponse());

        $this->oauth($browser)->authorize();

        $request = $browser->lastRequest();
        $this->assertSame(RequestMethods::POST, $request->method);
        $this->assertSame('https://gw.sandbox.gopay.com/api/oauth2/token', $request->url);
        $this->assertSame('Basic ' . base64_encode('client-id:client-secret'), $request->headers['Authorization']);
        $this->assertSame(GoPay::FORM, $request->headers['Content-Type']);
        $this->assertSame('grant_type=client_credentials&scope=payment-create', $request->body);
    }

    public function testSuccessfulAuthorizationReturnsValidToken(): void
    {
        $response = RecordingBrowser::tokenResponse('abc', 1800);

        $token = $this->oauth(new RecordingBrowser($response))->authorize();

        $this->assertSame('abc', $token->token);
        $this->assertSame($response, $token->response);
        $this->assertFalse($token->isExpired());
        $this->assertNotNull($token->expirationDate);
        $expiresIn = $token->expirationDate->getTimestamp() - time();
        $this->assertGreaterThan(1790, $expiresIn);
        $this->assertLessThanOrEqual(1800, $expiresIn);
    }

    public function testFailedAuthorizationReturnsExpiredTokenWithResponse(): void
    {
        $response = RecordingBrowser::response(403, ['errors' => [['error_name' => 'AUTH_WRONG_CREDENTIALS']]]);

        $token = $this->oauth(new RecordingBrowser($response))->authorize();

        $this->assertSame('', $token->token);
        $this->assertTrue($token->isExpired());
        $this->assertSame($response, $token->response);
    }

    public function testClientIdentifiesClientEnvironmentAndScope(): void
    {
        $this->assertSame(
            'client-id-https://gw.sandbox.gopay.com/api-payment-create',
            $this->oauth(new RecordingBrowser())->getClient()
        );
    }
}
