<?php

namespace GoPay\Tests\Unit;

use GoPay\GoPay;
use GoPay\OAuth2;
use GoPay\Token\AccessToken;
use GoPay\Token\CachedOAuth;
use GoPay\Token\InMemoryTokenCache;
use PHPUnit\Framework\TestCase;

class CachedOAuthTest extends TestCase
{
    private RecordingBrowser $browser;
    private InMemoryTokenCache $cache;
    private OAuth2 $oauth;
    private CachedOAuth $cachedOAuth;

    private function init(RecordingBrowser $browser): void
    {
        $this->browser = $browser;
        $this->cache = new InMemoryTokenCache();
        $this->oauth = new OAuth2(new GoPay(
            [
                'clientId' => 'client-id',
                'clientSecret' => 'client-secret',
                'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
                'scope' => 'payment-all',
                'language' => 'EN',
            ],
            $browser
        ));
        $this->cachedOAuth = new CachedOAuth($this->oauth, $this->cache);
    }

    public function testValidTokenIsTakenFromCache(): void
    {
        $this->init(new RecordingBrowser(RecordingBrowser::tokenResponse('first'), RecordingBrowser::tokenResponse('second')));

        $first = $this->cachedOAuth->authorize();
        $second = $this->cachedOAuth->authorize();

        $this->assertCount(1, $this->browser->requests);
        $this->assertSame('first', $second->token);
        $this->assertSame($first, $second);
    }

    public function testTokenIsStoredUnderClientKey(): void
    {
        $this->init(new RecordingBrowser(RecordingBrowser::tokenResponse('abc')));

        $token = $this->cachedOAuth->authorize();

        $this->assertSame($token, $this->cache->getAccessToken($this->oauth->getClient()));
    }

    public function testExpiredTokenIsRenewed(): void
    {
        $this->init(new RecordingBrowser(RecordingBrowser::tokenResponse('fresh')));
        $expired = new AccessToken();
        $expired->token = 'stale';
        $expired->expirationDate = new \DateTime('-1 minute');
        $this->cache->setAccessToken($this->oauth->getClient(), $expired);

        $token = $this->cachedOAuth->authorize();

        $this->assertCount(1, $this->browser->requests);
        $this->assertSame('fresh', $token->token);
        $this->assertSame($token, $this->cache->getAccessToken($this->oauth->getClient()));
    }

    public function testFailedTokenIsRequestedAgain(): void
    {
        $this->init(new RecordingBrowser(
            RecordingBrowser::response(500, []),
            RecordingBrowser::tokenResponse('abc')
        ));

        $failed = $this->cachedOAuth->authorize();
        $token = $this->cachedOAuth->authorize();

        $this->assertSame('', $failed->token);
        $this->assertSame('abc', $token->token);
        $this->assertCount(2, $this->browser->requests);
    }
}
