<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\TokenScope;

use GoPay\Http;
use GoPay\Token;

/*
define('__ROOT_', dirname(dirname(dirname(__FILE__))));
require_once(__ROOT_.'/src/Definition/Payment/Currency.php');
require_once(__ROOT_.'/src/Definition/Language.php');
require_once(__ROOT_.'/src/Definition/TokenScope.php');
require_once(__ROOT_.'/src/Http/JsonBrowser.php');
require_once(__ROOT_.'/src/Http/Log/NullLogger.php');
require_once(__ROOT_.'/src/Token/CachedOAuth.php');
require_once(__ROOT_.'/src/Token/InMemoryTokenCache.php');
*/

class EshopTest extends \PHPUnit_Framework_TestCase
{

    private $accessToken = 'copy token';
    private $config = [
            'scope' => TokenScope::ALL,
            'language' => Language::CZECH,
            'goid' => '8712700986',
            'timeout' => 30,
    ];

    private $gopay;
    private $auth;
    private $api;

    protected function setUp()
    {
        $browser = new Http\JsonBrowser(new Http\Log\NullLogger, $this->config['timeout']);
        $this->gopay = new GoPay($this->config, $browser);

        $this->auth = new Token\CachedOAuth(new OAuth2($this->gopay), new Token\InMemoryTokenCache);
//        $this->auth = new OAuth2($this->gopay);

        $this->api = new Eshop($this->gopay, $this->auth);
    }

    public function testGetPaymentInstruments()
    {
//        $response = $this->api->getPaymentInstruments(Currency::CZECH_CROWNS);

        $response = $this->gopay->call(
                "eshops/eshop/{$this->gopay->getConfig('goid')}/payment-instruments/" . Currency::CZECH_CROWNS,
                GoPay::FORM,
                "Bearer {$this->accessToken}",
                null
        );

        echo "Groups: {$response->json['groups']}";
    }
}