<?php


namespace GoPay;

use GoPay\Definition\RequestMethods;
use GoPay\Http\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;

use function PHPUnit\Framework\assertEquals;

class OAuth2Test extends TestCase
{
    private $config = [
        'clientId' => 'user',
        'clientSecret' => 'pass',
        'scope' => 'scope',
        'isProductionMode' => true,
    ];

    private $auth;
    private $gopay;

    protected function setUp(): void
    {


        $prophet = new Prophet();
        $this->gopay = $prophet->prophesize('GoPay\GoPay');
        foreach ($this->config as $key => $value) {
            $this->gopay->getConfig($key)->willReturn($value);
        }
        $this->auth = new OAuth2($this->gopay->reveal());
    }

    /** @dataProvider provideAccessToken */
    public function testShouldLoadAccessTokenWhenTokenIsExpired($statusCode, array $jsonResponse, $isExpired)
    {
        $response = new Response;
        $response->statusCode = $statusCode;
        $response->json = $jsonResponse;

        $this->gopay->call(
            'oauth2/token',
            GoPay::FORM,
            'Basic dXNlcjpwYXNz',
            RequestMethods::POST,
            ['grant_type' => 'client_credentials', 'scope' => $this->config['scope']]
        )->shouldBeCalled()->willReturn($response);

        $token = $this->auth->authorize();
        assertEquals($token->isExpired(), $isExpired);
    }

    public function testShouldUniquelyIdentifyCurrentClient()
    {
        assertEquals($this->auth->getClient(), 'user-1-scope');
    }

    public function provideAccessToken()
    {
        return [
            'success' => [200, ['access_token' => 'new token', 'expires_in' => 1800], false],
            'failure' => [400, ['error' => 'access_denied'], true]
        ];
    }
}
