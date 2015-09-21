<?php

namespace GoPay\Http;

class RequestHelper
{
    public function getBaseApiUrl($isProductionMode)
    {
        static $urls = [
            true => 'https://gate.gopay.cz/api/',
            false => 'https://gw.sandbox.gopay.com/api/'
        ];
        return $urls[(bool) $isProductionMode];
    }
}
