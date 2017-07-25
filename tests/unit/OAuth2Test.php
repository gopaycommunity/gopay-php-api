<?php

namespace GoPay;

use GoPay\Definition\RequestMethods;
use GoPay\Http\Response;

class OAuth2Test extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'clientId' => 'user',
        'clientSecret' => 'pass',
        'scope' => 'scope',
        'isProductionMode' => true,
    ];

    private $gopay;
    private $auth;

    protected function setUp()
    {
        $this->gopay = $this->prophesize('GoPay\GoPay');
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
        assertThat($token->isExpired(), is($isExpired));
    }

    public function testShouldUniquelyIdentifyCurrentClient()
    {
        assertThat($this->auth->getClient(), is('user-1-scope'));
    }

    public function provideAccessToken()
    {
        return [
            'success' => [200, ['access_token' => 'new token', 'expires_in' => 1800], false],
            'failure' => [400, ['error' => 'access_denied'], true]
        ];
    }
}
