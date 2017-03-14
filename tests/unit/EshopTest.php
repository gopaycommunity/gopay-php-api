<?php

namespace GoPay;

use GoPay\Token\AccessToken;
use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;

class EshopTest extends \PHPUnit_Framework_TestCase
{

    private $accessToken = '4sAH6lEyVpSz2lOowRRXpcLWcNRIlebOpC6vjPt+7r7t34QxwNPOb0RzWzuU2ar/vcVq7bCKWlN9lrzI5/cY6tfBbHgz/ZG5K9s5Mq2UvRI=';
    private $config = [
            'language' => Language::CZECH,
            'goid' => '8712700986'
    ];

    private $gopay;
    private $auth;
    private $api;

    protected function setUp()
    {
        $this->gopay = new GoPay($this->config, new Http\JsonBrowser(new Http\Log\PrintHttpRequest, 30));

        $this->auth = $this->prophesize('GoPay\Auth');
        $this->api = new Eshop($this->gopay->reveal(), $this->auth->reveal());
    }

    public function testGetPaymentInstruments()
    {
//        $this->givenAccessToken($this->accessToken);

//        $response = $this->api->getPaymentInstruments(Currency::CZECH_CROWNS);

        $response = $this->gopay->call(
                "eshops/eshop/{$this->gopay->getConfig('goid')}/payment-instruments/" . Currency::CZECH_CROWNS,
                GoPay::FORM,
                "Bearer {$this->accessToken}",
                null
        );

        echo "Groups: {$response->json['groups']}";
    }

    private function givenAccessToken($token)
    {
        $t = new AccessToken;
        $t->token = $token;
        $this->auth->authorize()->shouldBeCalled()->willReturn($t);
        return $t;
    }
}