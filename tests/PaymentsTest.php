<?php

namespace GoPay;

class PaymentsTest extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'clientID' => 'irrelevant id',
        'clientSecret' => 'irrelevant secret',
    ];
    private $id = 'irrelevant payment id';
    private $accessToken = 'irrelevant token';

    private $browser;
    private $api;

    protected function setUp()
    {
        $this->browser = $this->prophesize('GoPay\Browser');
        $this->api = new Payments($this->config, $this->browser->reveal());
        $this->api->setAccessToken($this->accessToken);
    }

    /** @dataProvider provideAccessToken */
    public function testShouldRequestAccessToken($statusCode, array $jsonResponse, $expectedToken)
    {
        $apiResponse = new Response;
        $apiResponse->statusCode = $statusCode;
        $apiResponse->json = $jsonResponse;
        $scope = PaymentScope::ALL;
        $this->browser->postJson(
            'https://gw.sandbox.gopay.com/api/oauth2/token',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'auth' => [$this->config['clientID'], $this->config['clientSecret']],
            ],
            ['grant_type' => 'client_credentials', 'scope' => $scope]
        )->shouldBeCalled()->willReturn($apiResponse);
        $this->api->authorize($scope);
        assertThat($this->api->getAccessToken(), is($expectedToken));
    }

    public function provideAccessToken()
    {
        return [
            'success' => [200, ['access_token' => 'new token', 'expires_in' => 100], 'new token'],
            'failure' => [400, ['error' => 'access_denied'], $this->accessToken]
        ];
    }

    /** @dataProvider provideApiMethods */
    public function testShouldCallApi($method, $params, $expectedRequest)
    {
        $this->browser->postJson(
            $expectedRequest[0],
            $expectedRequest[1],
            $expectedRequest[2]
        )->shouldBeCalled();
        call_user_func_array(array($this->api, $method), $params);
    }

    public function provideApiMethods()
    {
        return [
            'create payment' => [
                'createPayment',
                [['irrelevant payment']],
                [
                    'https://gw.sandbox.gopay.com/api/payments/payment',
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    ['irrelevant payment']
                ]
            ],
            'status of payment' => [
                'getStatus',
                [$this->id],
                [
                    "https://gw.sandbox.gopay.com/api/payments/payment/{$this->id}",
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    []
                ]
            ],
            'refund payment' => [
                'refund',
                [$this->id, 'amount'],
                [
                    "https://gw.sandbox.gopay.com/api/payments/payment/{$this->id}/refund",
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    ['amount' => 'amount']
                ]
            ]
        ];
    }
}
