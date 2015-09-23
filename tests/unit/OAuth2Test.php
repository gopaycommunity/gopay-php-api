<?php

namespace GoPay;

use GoPay\Http\Response;
use Prophecy\Argument;

class OAuth2Test extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'clientId' => 'user',
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
        $this->cache->getAccessToken()->shouldBeCalled();
        $this->getAccessTokenWhenExpirationIs(false);
    }

    /** @dataProvider provideAccessToken */
    public function testShouldRequestAccessTokenOnce($statusCode, array $jsonResponse, $isExpired)
    {
        $response = new Response;
        $response->statusCode = $statusCode;
        $response->json = $jsonResponse;

        $this->gopay->call(
            'oauth2/token',
            GoPay::FORM,
            [$this->config['clientId'], $this->config['clientSecret']],
            ['grant_type' => 'client_credentials', 'scope' => $this->config['scope']]
        )->shouldBeCalled()->willReturn($response);

        $this->cache
            ->setAccessToken(Argument::type('GoPay\Token\AccessToken'))->shouldBeCalled()
            ->will(function ($args) {
                $this->getAccessToken()->willReturn($args[0]);
            });

        $token = $this->getAccessTokenWhenExpirationIs(true);
        assertThat($token->isExpired(), is($isExpired));
    }

    public function provideAccessToken()
    {
        return [
            'success' => [200, ['access_token' => 'new token', 'expires_in' => 1800], false],
            'failure' => [400, ['error' => 'access_denied'], true]
        ];
    }

    private function getAccessTokenWhenExpirationIs($isExpired)
    {
        foreach ($this->config as $key => $value) {
            $this->gopay->getConfig($key)->willReturn($value);
        }

        $this->cache->setScope($this->config['scope'])->willReturn(null);
        $this->cache->isExpired()->willReturn($isExpired);

        $this->auth = new OAuth2($this->gopay->reveal(), $this->cache->reveal());
        return $this->auth->getAccessToken();
    }
}
