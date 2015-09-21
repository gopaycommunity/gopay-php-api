<?php

namespace GoPay;

class PaymentsTest extends \PHPUnit_Framework_TestCase
{
    private $config = array(
        'clientID' => 'irrelevant id',
        'clientSecret' => 'irrelevant secret',
    );

    private $browser;
    private $api;

    protected function setUp()
    {
        $this->browser = $this->prophesize('GoPay\Browser');
        $this->api = new Payments($this->config, $this->browser->reveal());
    }

    public function testShouldCreateAccessToken()
    {
        $this->browser->getOAuthToken(
            'https://gw.sandbox.gopay.com/api/oauth2/token',
            'grant_type=client_credentials&scope=payment-create',
            array(
                'auth' => [$this->config['clientID'], $this->config['clientSecret']],
            )
        )->shouldBeCalled();
        $this->api->authorize();
    }
}
