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

    /** @dataProvider provideAccessToken */
    public function testShouldRequestAccessToken($statusCode, $hasSucceed)
    {
        $jsonResponse = ['irrelevant response'];
        $scope = PaymentScope::ALL;
        $this->browser->postJson(
            'https://gw.sandbox.gopay.com/api/oauth2/token',
            ['grant_type' => 'client_credentials', 'scope' => $scope],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'auth' => [$this->config['clientID'], $this->config['clientSecret']],
            ]
        )->shouldBeCalled()->willReturn([$statusCode, $jsonResponse]);
        $response = $this->api->authorize($scope);

        assertThat($response, anInstanceOf('GoPay\Response'));
        assertThat($response->hasSucceed, is($hasSucceed));
        assertThat($response->json, is($jsonResponse));
    }

    public function provideAccessToken()
    {
        return [
            'success' => [200, true],
            'failure' => [400, false]
        ];
    }

    /** @dataProvider providePayment */
    public function testShouldCreateStandardPayment($statusCode, $hasSucceed)
    {
        $jsonResponse = ['irrelevant response'];
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
        )->shouldBeCalled()->willReturn([$statusCode, $jsonResponse]);
        $response = $this->api->createPayment($payment, $token);

        assertThat($response->hasSucceed, is($hasSucceed));
        assertThat($response->json, is($jsonResponse));
    }

    /** @dataProvider providePayment */
    public function testShouldGetStatusOfPayment($statusCode, $hasSucceed)
    {
        $jsonResponse = ['irrelevant response'];
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
        )->shouldBeCalled()->willReturn([$statusCode, $jsonResponse]);
        $response = $this->api->getStatus($id, $token);

        assertThat($response->hasSucceed, is($hasSucceed));
        assertThat($response->json, is($jsonResponse));
    }

    /** @dataProvider providePayment */
    public function testShouldRefundPayment($statusCode, $hasSucceed)
    {
        $jsonResponse = ['irrelevant response'];
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
        )->shouldBeCalled()->willReturn([$statusCode, $jsonResponse]);
        $response = $this->api->refund($id, $amount, $token);

        assertThat($response->hasSucceed, is($hasSucceed));
        assertThat($response->json, is($jsonResponse));
    }

    public function providePayment()
    {
        return [
            'success' => [200, true],
            'failure - validation' => [409, false]
        ];
    }
}
