<?php

namespace GoPay;

use GoPay\Definition\Language;

class TestUtils
{

    const GO_ID = '8761908826';
    const CLIENT_ID = 'app@musicshop.cz';
    const CLIENT_SECRET = 'VpnJVcTn';

    const GO_ID_EET = '8289213768';
    const CLIENT_ID_EET = '1365575992';
    const CLIENT_SECRET_EET = 'NUVsrv4W';


    public static function setup()
    {
        $gopay = payments([
            'goid' => self::GO_ID,
            'clientId' => self::CLIENT_ID,
            'clientSecret' => self::CLIENT_SECRET,
            'gatewayUrl' => 'https://gw.pmaryska.dev.gopay.com/api',
            'language' => Language::CZECH
        ]);
        return $gopay;
    }

    public static function setupEET()
    {
        $gopay = payments([
            'goid' => self::GO_ID_EET,
            'clientId' => self::CLIENT_ID_EET,
            'clientSecret' => self::CLIENT_SECRET_EET,
            'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
            'language' => Language::CZECH
        ]);
        return $gopay;
    }
}
