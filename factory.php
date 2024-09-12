<?php

namespace GoPay;

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
 * @param array|Config $userConfig
 * @param array $userServices
 * @return PaymentsSupercash
 */
function paymentsSupercash(array|Config $userConfig, array $userServices = [])
{
    $config = Config::parseUserConfig($userConfig);
    $services = $userServices + [
                    'cache' => new Token\InMemoryTokenCache,
                    'logger' => new Http\Log\NullLogger
            ];
    $browser = new Http\JsonBrowser($services['logger'], $config['timeout']);
    $gopay = new GoPay($config, $browser);
    $auth = new Token\CachedOAuth(new OAuth2($gopay), $services['cache']);
    return new PaymentsSupercash($gopay, $auth);
}

/** Symfony container needs class for factory :( */
class Api
{
    public static function payments(array|Config $config, array $services = [])
    {
        return payments($config, $services);
    }
}
