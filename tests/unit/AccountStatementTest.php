<?php

namespace GoPay;

use GoPay\Definition\Account\StatementGeneratingFormat;
use GoPay\Definition\Payment\Currency;
use GoPay\Token\AccessToken;
use GoPay\Definition\Language;

class AccountStatementTest extends \PHPUnit_Framework_TestCase
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
        $this->gopay = new GoPay($this->config, new Http\OctetStreamBrowser(new Http\Log\NullLogger, 30));

        $this->auth = $this->prophesize('GoPay\Auth');
        $this->api = new AccountStatement($this->gopay->reveal(), $this->auth->reveal());
    }

    public function testGetPaymentInstruments()
    {
//        $this->givenAccessToken($this->accessToken);

        $accountStatement = [
            'date_from' => '2017-01-01',
            'date_to' => '2017-01-27',
            'goid' => $this->gopay->getConfig('goid'),
            'currency' => Currency::CZECH_CROWNS,
            'format' => StatementGeneratingFormat::CSV_A,
        ];

//        $response = $this->api->getAccountStatement($accountStatement);

        $response = $this->gopay->call(
                "accounts/account-statement",
                GoPay::JSON,
                "Bearer {$this->accessToken}",
                $accountStatement
        );

        echo "Byte pole: {$response->rawBody}";
    }

    private function givenAccessToken($token)
    {
        $t = new AccessToken;
        $t->token = $token;
        $this->auth->authorize()->shouldBeCalled()->willReturn($t);
        return $t;
    }




}