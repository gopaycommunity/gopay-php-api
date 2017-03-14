<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;

class EshopTest extends \PHPUnit_Framework_TestCase
{

    private $accessToken = 'copy token';
    private $config = [
            'scope' => Definition\TokenScope::ALL,
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