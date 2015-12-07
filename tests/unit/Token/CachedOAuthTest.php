<?php

namespace GoPay\Token;

class CachedOAuthTest extends \PHPUnit_Framework_TestCase
{
    private $token;
    private $cache;
    private $isTokenInCache = true;
    private $reauthorizedToken;

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
        assertThat($this->cache->getAccessToken(), is(notNullValue()));
    }

    private function tokenShouldBe($expectedToken)
    {
        $client = 'irrelevant client';
        if ($this->isTokenInCache) {
            $this->cache->setAccessToken($this->token);
        }

        $oauth = $this->prophesize('GoPay\OAuth2');
        $oauth->authorize()->willReturn($this->reauthorizedToken);
        $oauth->getClient()->willReturn($client);

        $auth = new CachedOAuth($oauth->reveal(), $this->cache);
        assertThat($auth->authorize(), identicalTo($expectedToken));
    }
}
