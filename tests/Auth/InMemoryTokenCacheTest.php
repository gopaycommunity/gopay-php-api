<?php

namespace GoPay\Auth;

class InMemoryTokenCacheTest extends \PHPUnit_Framework_TestCase
{
    private $cache;

    protected function setUp()
    {
        $this->cache = new InMemoryTokenCache;
    }

    public function testNotInitiliazedCacheHasEmptyToken()
    {
        $this->assertExpiredToken();
    }

    public function testExpiredTokenIsIgnored()
    {
        $expiredDate = new \DateTime('now - 1 month');
        $this->cache->setAccessToken('irrelevant token', $expiredDate);
        $this->assertExpiredToken();
    }

    public function testIsNotExpiredWhenTokenIsSetAndDateIsInTheFuture()
    {
        $this->cache->setAccessToken('irrelevant token', new \DateTime('now + 1 day'));
        assertThat($this->cache->isExpired(), is(false));
        assertThat($this->cache->getAccessToken(), not(emptyString()));
    }

    private function assertExpiredToken()
    {
        assertThat($this->cache->isExpired(), is(true));
        assertThat($this->cache->getAccessToken(), is(emptyString()));
    }
}
