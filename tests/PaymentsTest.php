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

    /** @dataProvider providePayment */
    public function testShouldCreateStandardPayment($statusCode, $hasSucceed)
    {
        $jsonResponse = ['irrelevant response'];
        $token = 'irrelevant access token';
        $payment = ['irrelevant data'];
        $this->browser->postJson(
            'https://gw.sandbox.gopay.com/api/payments/payment',
            $payment,
            array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$token}"
            )
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
            array(),
            array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$token}"
            )
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
            array('amount' => $amount),
            array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer {$token}"
            )
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
