<?php

namespace GoPay\Tests\Unit;

use GoPay\Config;
use GoPay\Definition\Language;
use GoPay\Definition\TokenScope;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testParseArrayConfigAddsDefaults(): void
    {
        $config = Config::parseUserConfig(['goid' => '123']);

        $this->assertSame(
            [
                'goid' => '123',
                'scope' => TokenScope::ALL,
                'language' => Language::ENGLISH,
                'timeout' => 30,
            ],
            $config
        );
    }

    public function testParseArrayConfigKeepsUserValues(): void
    {
        $config = Config::parseUserConfig([
            'scope' => TokenScope::CREATE_PAYMENT,
            'language' => Language::CZECH,
            'timeout' => 5,
        ]);

        $this->assertSame(TokenScope::CREATE_PAYMENT, $config['scope']);
        $this->assertSame(Language::CZECH, $config['language']);
        $this->assertSame(5, $config['timeout']);
    }

    public function testParseArrayConfigWithoutDefaults(): void
    {
        $this->assertSame(['goid' => '123'], Config::parseUserConfig(['goid' => '123'], false));
    }

    public function testParseConfigObject(): void
    {
        $config = new Config('client-id', 'client-secret', '123', 'https://gw.sandbox.gopay.com/api');

        $this->assertSame(
            [
                'goid' => '123',
                'clientId' => 'client-id',
                'clientSecret' => 'client-secret',
                'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
                'scope' => TokenScope::ALL,
                'language' => Language::ENGLISH,
                'timeout' => 30,
            ],
            Config::parseUserConfig($config)
        );
    }

    public function testFromArrayToArrayRoundTrip(): void
    {
        $array = [
            'goid' => '123',
            'clientId' => 'client-id',
            'clientSecret' => 'client-secret',
            'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
            'scope' => TokenScope::CREATE_PAYMENT,
            'language' => Language::CZECH,
            'timeout' => 5,
        ];

        $this->assertSame($array, Config::fromArray($array)->toArray());
    }

    public function testFromArrayKeepsDefaultsForMissingValues(): void
    {
        $config = Config::fromArray(['goid' => '123']);

        $this->assertSame('123', $config->goid);
        $this->assertNull($config->clientId);
        $this->assertSame(TokenScope::ALL, $config->scope);
        $this->assertSame(Language::ENGLISH, $config->language);
        $this->assertSame(30, $config->timeout);
    }
}
