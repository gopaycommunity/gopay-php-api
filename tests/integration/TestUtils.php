<?php

namespace GoPay;

use GoPay\Definition\Language;

class TestUtils
{
    public const GO_ID = '8583067438';
    public const CLIENT_ID = '1223619925';
    public const CLIENT_SECRET = '6vkhVP8c';

    public const GO_ID_EET = '8289213768';
    public const CLIENT_ID_EET = '1365575992';
    public const CLIENT_SECRET_EET = 'NUVsrv4W';


    public static function setup(): Payments
    {
        $gopay = payments([
            'goid' => self::GO_ID,
            'clientId' => self::CLIENT_ID,
            'clientSecret' => self::CLIENT_SECRET,
            'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
            'language' => Language::CZECH,
        ]);
        return $gopay;
    }

    public static function setupEET(): Payments
    {
        $gopay = payments([
            'goid' => self::GO_ID_EET,
            'clientId' => self::CLIENT_ID_EET,
            'clientSecret' => self::CLIENT_SECRET_EET,
            'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
            'language' => Language::CZECH,
        ]);
        return $gopay;
    }
}
