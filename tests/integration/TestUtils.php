<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Language;
use GoPay\Payments;

use function GoPay\payments;

class TestUtils
{
    public const GO_ID = '8583067438';
    public const CLIENT_ID = '1223619925';
    public const CLIENT_SECRET = '6vkhVP8c';

    public const GO_ID_EET = '8289213768';
    public const CLIENT_ID_EET = '1365575992';
    public const CLIENT_SECRET_EET = 'NUVsrv4W';


    /**
     * Prints debug output only when the GOPAY_TEST_DEBUG environment variable is set.
     * Writes to STDERR, so the output is not captured by PHPUnit and does not make tests risky.
     */
    public static function debug(mixed $data): void
    {
        if (getenv('GOPAY_TEST_DEBUG')) {
            fwrite(STDERR, (is_string($data) ? $data : print_r($data, true)) . "\n");
        }
    }

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
