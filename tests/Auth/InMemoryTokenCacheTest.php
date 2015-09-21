<?php

namespace GoPay\Auth;

class InMemoryTokenCacheTest extends \PHPUnit_Framework_TestCase
{
    private $cache;

    protected function setUp()
    {
        $this->cache = new InMemoryTokenCache;
        $this->cache->setScope(PaymentScope::ALL);
    }

    /** @dataProvider provideScope */
    public function testNotInitiliazedCacheHasEmptyToken($scope)
    {
        $this->cache->setScope($scope);
        $this->tokenShouldBeExpired();
    }

    public function testExpiredTokenIsIgnored()
    {
        $expiredDate = new \DateTime('now - 1 month');
        $this->cache->setAccessToken('irrelevant token', $expiredDate);
        $this->tokenShouldBeExpired();
    }

    public function testIsNotExpiredWhenTokenIsSetAndDateIsInTheFuture()
    {
        $this->cache->setAccessToken('irrelevant token', new \DateTime('now + 1 day'));
        $this->tokenShouldBeValid();
    }

    public function testExpirationTokenIsDifferentForEachScope()
    {
        $this->cache->setScope(PaymentScope::ALL);
        $this->cache->setAccessToken('irrelevant token', new \DateTime('now + 1 day'));
        $this->tokenShouldBeValid();
        $this->cache->setScope(PaymentScope::CREATE);
        $this->tokenShouldBeExpired();
    }

    private function tokenShouldBeExpired()
    {
        assertThat($this->cache->isExpired(), is(true));
        assertThat($this->cache->getAccessToken(), is(emptyString()));
    }

    private function tokenShouldBeValid()
    {
        assertThat($this->cache->isExpired(), is(false));
        assertThat($this->cache->getAccessToken(), not(emptyString()));
    }

    public function provideScope()
    {
        return [
            [PaymentScope::ALL],
            [PaymentScope::CREATE],
        ];
    }
}
