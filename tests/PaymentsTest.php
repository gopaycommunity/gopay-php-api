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

    /** @dataProvider provideAccessToken */
    public function testShouldRequestAccessToken($statusCode, $jsonResponse, $hasSucceed)
    {
        $scope = PaymentScope::ALL;
        $this->browser->getOAuthToken(
            'https://gw.sandbox.gopay.com/api/oauth2/token',
            "grant_type=client_credentials&scope={$scope}",
            array(
                'auth' => [$this->config['clientID'], $this->config['clientSecret']],
            )
        )->shouldBeCalled()->willReturn([$statusCode, $jsonResponse]);
        $response = $this->api->authorize($scope);

        assertThat($response, anInstanceOf('GoPay\Response'));
        assertThat($response->hasSucceed, is($hasSucceed));
        assertThat($response->json, is($jsonResponse));
    }

    public function provideAccessToken()
    {
        return [
            'success' => [200, ['access_token' => 'token', 'expires_in' => 100], true],
            'failure' => [400, ['error' => 'access_denied'], false]
        ];
    }
}
