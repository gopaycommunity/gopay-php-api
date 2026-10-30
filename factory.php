<?php

namespace GoPay;

/**
 * @param array<string, mixed>|Config $userConfig
 * @param array{cache?: Token\TokenCache, logger?: Http\Log\Logger} $userServices
 * @return Payments
 */
function payments(array|Config $userConfig, array $userServices = [])
{
    $config = Config::parseUserConfig($userConfig);
    $services = $userServices + [
        'cache' => new Token\InMemoryTokenCache,
        'logger' => new Http\Log\NullLogger
    ];
    $browser = new Http\JsonBrowser($services['logger'], $config['timeout']);
    $gopay = new GoPay($config, $browser);
    $auth = new Token\CachedOAuth(new OAuth2($gopay), $services['cache']);
    return new Payments($gopay, $auth);
}

/**
 * @deprecated Supercash payments are no longer supported
 * @param array<string, mixed>|Config $userConfig
 * @param array{cache?: Token\TokenCache, logger?: Http\Log\Logger} $userServices
 * @return PaymentsSupercash
 */
function paymentsSupercash(array|Config $userConfig, array $userServices = []) // @phpstan-ignore class.notFound
{
    $config = Config::parseUserConfig($userConfig);
    $services = $userServices + [
                    'cache' => new Token\InMemoryTokenCache,
                    'logger' => new Http\Log\NullLogger
            ];
    $browser = new Http\JsonBrowser($services['logger'], $config['timeout']);
    $gopay = new GoPay($config, $browser);
    $auth = new Token\CachedOAuth(new OAuth2($gopay), $services['cache']);
    // Class was removed together with Supercash support, function is kept for BC
    return new PaymentsSupercash($gopay, $auth); // @phpstan-ignore class.notFound
}

/** Symfony container needs class for factory :( */
class Api
{
    /**
     * @param array<string, mixed>|Config $config
     * @param array{cache?: Token\TokenCache, logger?: Http\Log\Logger} $services
     * @return Payments
     */
    public static function payments(array|Config $config, array $services = [])
    {
        return payments($config, $services);
    }
}
