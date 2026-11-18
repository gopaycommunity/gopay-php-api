<?php

namespace GoPay\Tests\Unit;

use GoPay\Api;
use GoPay\Config;
use GoPay\Definition\Language;

use function GoPay\payments;

use GoPay\Token\CachedOAuth;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /** @var array<string, string> */
    private const CONFIG = [
        'goid' => '123',
        'clientId' => 'client-id',
        'clientSecret' => 'client-secret',
        'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
    ];

    public function testPaymentsAppliesConfigDefaults(): void
    {
        $payments = payments(self::CONFIG);

        $this->assertInstanceOf(CachedOAuth::class, $payments->getAuth());
        $this->assertSame('payment-all', $payments->getGopay()->getConfig('scope'));
        $this->assertSame(Language::ENGLISH, $payments->getGopay()->getConfig('language'));
        $this->assertSame(30, $payments->getGopay()->getConfig('timeout'));
    }

    public function testPaymentsAcceptsConfigObject(): void
    {
        $payments = payments(Config::fromArray(self::CONFIG));

        $this->assertSame('123', $payments->getGopay()->getConfig('goid'));
    }

    public function testApiPaymentsIsAliasOfPaymentsFunction(): void
    {
        $payments = Api::payments(self::CONFIG);

        $this->assertSame('123', $payments->getGopay()->getConfig('goid'));
    }
}
