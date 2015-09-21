<?php

namespace GoPay;

use GoPay\Http\Response;
use GoPay\Token\InMemoryTokenCache;

class OAuth2Test extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'clientID' => 'user',
        'clientSecret' => 'pass',
        'scope' => 'irrelevant scope'
    ];

    private $gopay;
    private $auth;

    protected function setUp()
    {
        $cache = new InMemoryTokenCache();
        $this->gopay = $this->prophesize('GoPay\GoPay');
        foreach ($this->config as $key => $value) {
            $this->gopay->getConfig($key)->willReturn($value);
        }
        $this->auth = new OAuth2($this->gopay->reveal(), $cache);
    }

    /** @dataProvider provideAccessToken */
    public function testShouldRequestAccessTokenOnce($statusCode, array $jsonResponse, $expectedCalls, $expectedToken)
    {
        $apiResponse = new Response;
        $apiResponse->statusCode = $statusCode;
        $apiResponse->json = $jsonResponse;

        $this->gopay->call(
            'oauth2/token',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => [$this->config['clientID'], $this->config['clientSecret']],
            ],
            ['grant_type' => 'client_credentials', 'scope' => $this->config['scope']]
        )->shouldBeCalledTimes($expectedCalls)->willReturn($apiResponse);

        assertThat($this->auth->getAccessToken(), is($expectedToken));
        assertThat($this->auth->getAccessToken(), is($expectedToken));
    }

    public function provideAccessToken()
    {
        return [
            'success' => [200, ['access_token' => 'new token', 'expires_in' => 1800], 1, 'new token'],
            'failure' => [400, ['error' => 'access_denied'], 2, emptyString()]
        ];
    }
}
