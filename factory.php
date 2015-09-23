<?php

namespace GoPay;

function payments(array $config, array $userServices = [])
{
    $services = $userServices + [
        'cache' => new Token\InMemoryTokenCache,
        'logger' => new Http\Log\NullLogger
    ];
    $browser = new Http\JsonBrowser($services['logger']);
    $gopay = new GoPay($config, $browser);
    $auth = new OAuth2($gopay, $services['cache']);
    return new Payments($gopay, $auth);
}

/** Symfony container needs class for factory :( */
class Api
{
    public static function payments(array $config, array $services = [])
    {
        return payments($config, $services);
    }
}
