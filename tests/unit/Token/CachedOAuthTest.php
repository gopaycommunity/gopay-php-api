<?php

namespace GoPay\Token;

class CachedOAuthTest extends \PHPUnit_Framework_TestCase
{
    private $token;
    private $cache;
    private $isTokenInCache = true;
    private $reauthorizedToken;
    private $client = 'irrelevant client';

    protected function setUp()
    {
        $this->token = new AccessToken();
        $this->cache = new InMemoryTokenCache();
        $this->reauthorizedToken = new AccessToken();
    }

    public function testShouldUseUnexpiredToken()
    {
        $this->token->token = 'irrelevant token';
        $this->token->expirationDate = new \DateTime('now + 1 day');
        $this->tokenShouldBe($this->token);
    }

    public function testShouldReauthorizeWhenTokenIsEmpty()
    {
        $this->token->token = '';
        $this->tokenShouldBe($this->reauthorizedToken);
    }

    public function testShouldReauthorizeWhenExpirationIsEmpty()
    {
        $this->token->token = 'irrelevant token';
        $this->token->expirationDate = null;
        $this->tokenShouldBe($this->reauthorizedToken);
    }

    public function testShouldReauthorizeWhenTokenIsExpired()
    {
        $this->token->token = 'irrelevant token';
        $this->token->expirationDate = new \DateTime('now - 1 day');
        $this->tokenShouldBe($this->reauthorizedToken);
    }

    public function testShouldReauthorizeWhenCacheIsEmpty()
    {
        $this->isTokenInCache = false;
        $this->tokenShouldBe($this->reauthorizedToken);
    }

    public function testShouldStoreTokenInCache()
    {
        $this->isTokenInCache = false;
        $this->tokenShouldBe($this->reauthorizedToken);
        assertThat($this->cache->getAccessToken($this->client), is(notNullValue()));
    }

    private function tokenShouldBe($expectedToken)
    {
        if ($this->isTokenInCache) {
            $this->cache->setAccessToken($this->client, $this->token);
        }

        $oauth = $this->prophesize('GoPay\OAuth2');
        $oauth->authorize()->willReturn($this->reauthorizedToken);
        $oauth->getClient()->willReturn($this->client);

        $auth = new CachedOAuth($oauth->reveal(), $this->cache);
        assertThat($auth->authorize(), identicalTo($expectedToken));
    }
}
