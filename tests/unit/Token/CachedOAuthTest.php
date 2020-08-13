<?php

namespace GoPay\Token;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

class CachedOAuthTest extends TestCase
{
    private $token;
    private $cache;
    private $isTokenInCache = true;
    private $reauthorizedToken;
    private $client = 'irrelevant client';

    protected function setUp(): void
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
        assertNotNull($this->cache->getAccessToken($this->client));
    }

    private function tokenShouldBe($expectedToken)
    {
        if ($this->isTokenInCache) {
            $this->cache->setAccessToken($this->client, $this->token);
        }

        $prophet = new Prophet();;
        $oauth = $prophet->prophesize('GoPay\OAuth2');
        $oauth->authorize()->willReturn($this->reauthorizedToken);
        $oauth->getClient()->willReturn($this->client);

        $auth = new CachedOAuth($oauth->reveal(), $this->cache);
        assertEquals($auth->authorize(), $expectedToken);
    }
}
