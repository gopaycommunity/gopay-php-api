<?php

namespace GoPay;

class PaymentsTest extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'clientID' => 'irrelevant id',
        'clientSecret' => 'irrelevant secret',
    ];

    private $browser;
    private $api;

    protected function setUp()
    {
        $this->browser = $this->prophesize('GoPay\Browser');
        $this->api = new Payments($this->config, $this->browser->reveal());
    }

    public function testShouldRequestAccessToken()
    {
        $scope = PaymentScope::ALL;
        $this->browser->postJson(
            'https://gw.sandbox.gopay.com/api/oauth2/token',
            ['grant_type' => 'client_credentials', 'scope' => $scope],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'auth' => [$this->config['clientID'], $this->config['clientSecret']],
            ]
        )->shouldBeCalled()->willReturn(new Response);
        $this->api->authorize($scope);
    }

    public function testShouldCreateStandardPayment()
    {
        $token = 'irrelevant access token';
        $payment = ['irrelevant data'];
        $this->browser->postJson(
            'https://gw.sandbox.gopay.com/api/payments/payment',
            $payment,
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ]
        )->shouldBeCalled()->willReturn(new Response);
        $this->api->createPayment($payment, $token);
    }

    public function testShouldGetStatusOfPayment()
    {
        $token = 'irrelevant access token';
        $id = 'irrelevant id';
        $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/payments/payment/{$id}",
            [],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$token}"
            ]
        )->shouldBeCalled()->willReturn(new Response);
        $this->api->getStatus($id, $token);
    }

    public function testShouldRefundPayment()
    {
        $token = 'irrelevant access token';
        $id = 'irrelevant id';
        $amount = 'irrelevant amount';
        $this->browser->postJson(
            "https://gw.sandbox.gopay.com/api/payments/payment/{$id}/refund",
            ['amount' => $amount],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$token}"
            ]
        )->shouldBeCalled()->willReturn(new Response);
        $this->api->refund($id, $amount, $token);
    }
}
