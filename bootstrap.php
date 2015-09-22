<?php

namespace GoPay;

function payments(array $config, Token\TokenCache $cache = null)
{
    $cache = $cache ?: new Token\InMemoryTokenCache;
    $browser = new Http\JsonBrowser();
    $gopay = new GoPay($config, $browser);
    $auth = new OAuth2($gopay, $cache);
    return new Payments($gopay, $auth);
}
