<?php

namespace GoPay;

require_once __DIR__ . '/../../vendor/autoload.php';

use GoPay\Definition\Language;

class TestUtils
{

    const GO_ID = '8712700986';
    const CLIENT_ID = '1689337452';
    const CLIENT_SECRET = 'CKr7FyEE';

    const GO_ID_EET = '8289213768';
    const CLIENT_ID_EET = '1365575992';
    const CLIENT_SECRET_EET = 'NUVsrv4W';


    public static function setup()
    {
        $gopay = payments([
                'goid' => self::GO_ID,
                'clientId' => self::CLIENT_ID,
                'clientSecret' => self::CLIENT_SECRET,
                'isProductionMode' => false,
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
                'isProductionMode' => false,
                'language' => Language::CZECH
        ]);
        return $gopay;
    }

    public static function setupSupercash()
    {
        $gopay = paymentsSupercash([
                'goid' => self::GO_ID,
                'clientId' => self::CLIENT_ID,
                'clientSecret' => self::CLIENT_SECRET,
                'isProductionMode' => false,
                'language' => Language::CZECH
        ]);
        return $gopay;
    }

}
