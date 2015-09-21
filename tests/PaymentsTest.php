<?php

namespace GoPay;

class PaymentsTest extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'clientID' => 'irrelevant id',
        'clientSecret' => 'irrelevant secret',
    ];
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
            ['grant_type' => 'client_credentials', 'scope' => $scope],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'auth' => [$this->config['clientID'], $this->config['clientSecret']],
            ]
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

    public function testShouldCreateStandardPayment()
    {
        $payment = ['irrelevant data'];
        $this->browser->postJson(
            'https://gw.sandbox.gopay.com/api/payments/payment',
            $payment,
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->accessToken}"
            ]
        )->shouldBeCalled()->willReturn(new Response);
        $this->api->createPayment($payment);
    }

    public function testShouldGetStatusOfPayment()
    {
        $id = 'irrelevant id';
        $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/payments/payment/{$id}",
            [],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$this->accessToken}"
            ]
        )->shouldBeCalled()->willReturn(new Response);
        $this->api->getStatus($id);
    }

    public function testShouldRefundPayment()
    {
        $id = 'irrelevant id';
        $amount = 'irrelevant amount';
        $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/payments/payment/{$id}/refund",
            ['amount' => $amount],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$this->accessToken}"
            ]
        )->shouldBeCalled()->willReturn(new Response);
        $this->api->refund($id, $amount);
    }
}
