<?php

namespace GoPay;

use GoPay\Http\Response;
use Prophecy\Argument;

class OAuth2Test extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'clientID' => 'user',
        'clientSecret' => 'pass',
        'scope' => 'irrelevant scope'
    ];

    private $gopay;
    private $cache;
    private $auth;

    protected function setUp()
    {
        $this->gopay = $this->prophesize('GoPay\GoPay');
        $this->cache = $this->prophesize('GoPay\Token\TokenCache');
    }

    public function testNoRequestWhenCachedTokenIsValid()
    {
        $this->getAccessTokenWhenExpirationIs(false);
    }

    /** @dataProvider provideAccessToken */
    public function testShouldRequestAccessTokenOnce($statusCode, array $jsonResponse, $isTokenLoaded)
    {
        $response = new Response;
        $response->statusCode = $statusCode;
        $response->json = $jsonResponse;

        $this->gopay->call(
            'oauth2/token',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => [$this->config['clientID'], $this->config['clientSecret']],
            ],
            ['grant_type' => 'client_credentials', 'scope' => $this->config['scope']]
        )->shouldBeCalled()->willReturn($response);

        if ($isTokenLoaded) {
            $this->cache->setAccessToken(Argument::cetera())->shouldBeCalled();
        }

        $this->getAccessTokenWhenExpirationIs(true);
    }

    public function provideAccessToken()
    {
        return [
            'success' => [200, ['access_token' => 'new token', 'expires_in' => 1800], true],
            'failure' => [400, ['error' => 'access_denied'], false]
        ];
    }

    private function getAccessTokenWhenExpirationIs($isExpired)
    {
        foreach ($this->config as $key => $value) {
            $this->gopay->getConfig($key)->willReturn($value);
        }

        $this->cache->setScope($this->config['scope'])->willReturn(null);
        $this->cache->isExpired()->willReturn($isExpired);
        $this->cache->getAccessToken()->shouldBeCalled();

        $this->auth = new OAuth2($this->gopay->reveal(), $this->cache->reveal());
        $this->auth->getAccessToken();
    }
}
