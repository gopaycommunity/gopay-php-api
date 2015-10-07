<?php

namespace GoPay\Token;

use GoPay\Definition\TokenScope;

class InMemoryTokenCacheTest extends \PHPUnit_Framework_TestCase
{
    private $cache;

    protected function setUp()
    {
        $this->cache = new InMemoryTokenCache;
        $this->cache->setClient(TokenScope::ALL);
    }

    /** @dataProvider provideScope */
    public function testNotInitiliazedCacheIsExpired($scope)
    {
        $this->cache->setClient($scope);
        $this->tokenShouldBeExpired();
    }

    public function testDateIsExpiredButTokenIsNotOverriden()
    {
        $expiredDate = new \DateTime('now - 1 month');
        $this->givenToken('irrelevant token', $expiredDate);
        $this->tokenShouldBeExpired('irrelevant token');
    }

    public function testIsNotExpiredWhenTokenIsSetAndDateIsInTheFuture()
    {
        $this->givenToken('irrelevant token', new \DateTime('now + 1 day'));
        $this->tokenShouldBeValid();
    }

    public function testExpirationTokenIsDifferentForEachScope()
    {
        $this->cache->setClient(TokenScope::ALL);
        $this->givenToken('irrelevant token', new \DateTime('now + 1 day'));
        $this->tokenShouldBeValid();
        $this->cache->setClient(TokenScope::CREATE_PAYMENT);
        $this->tokenShouldBeExpired();
    }

    public function givenToken($token, $expiration)
    {
        $t = new AccessToken();
        $t->token = $token;
        $t->expirationDate = $expiration;
        $this->cache->setAccessToken($t);
    }

    private function tokenShouldBeExpired($expectedToken = null)
    {
        $expectedToken = $expectedToken ?: emptyString();
        assertThat($this->cache->isExpired(), is(true));
        assertThat($this->cache->getAccessToken()->token, is($expectedToken));
    }

    private function tokenShouldBeValid()
    {
        assertThat($this->cache->isExpired(), is(false));
        assertThat($this->cache->getAccessToken()->token, not(emptyString()));
    }

    public function provideScope()
    {
        return [
            [TokenScope::ALL],
            [TokenScope::CREATE_PAYMENT],
        ];
    }
}
