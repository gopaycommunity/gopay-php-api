<?php

namespace GoPay;

use GoPay\Definition\Account\StatementGeneratingFormat;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Language;

class AccountStatementTest extends \PHPUnit_Framework_TestCase
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
        $browser = new Http\OctetStreamBrowser(new Http\Log\NullLogger, $this->config['timeout']);
        $this->gopay = new GoPay($this->config, $browser);

        $this->auth = new Token\CachedOAuth(new OAuth2($this->gopay), new Token\InMemoryTokenCache);

        $this->api = new AccountStatement($this->gopay, $this->auth);
    }

    public function testGetAccountStatement()
    {
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

}