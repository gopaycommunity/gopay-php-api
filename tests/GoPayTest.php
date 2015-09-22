<?php

namespace GoPay;

use Unirest\Method;
use GoPay\Http\Request;

class GoPayTest extends \PHPUnit_Framework_TestCase
{
    private $urlPath = 'irrelevant-path';

    /** @dataProvider provideRequest */
    public function testShouldCompleteRequest($isProductionMode, $headers, $body, Request $expectedRequest)
    {
        $expectedRequest->headers = array_merge($headers, $expectedRequest->headers);

        $browser = $this->prophesize('GoPay\Http\JsonBrowser');
        $browser->send($expectedRequest)->shouldBeCalled();

        $gopay = new GoPay(['isProductionMode' => $isProductionMode], $browser->reveal());
        $gopay->call($this->urlPath, $headers, $body);
    }

    public function provideRequest()
    {
        return [
            'get form in production' => [
                true,
                ['Content-Type' => GoPay::FORM, 'Authorization' => 'Bearer irrelevantToken'],
                null,
                $this->buildRequest(
                    Method::GET,
                    'https://gate.gopay.cz/api/',
                    'Bearer irrelevantToken'
                )
            ],
            'send form in production' => [
                true,
                ['Content-Type' => GoPay::FORM, 'Authorization' => 'Bearer irrelevantToken'],
                ['key' => 'value'],
                $this->buildRequest(
                    Method::POST,
                    'https://gate.gopay.cz/api/',
                    'Bearer irrelevantToken',
                    'key=value'
                )
            ],
            'send json in test' => [
                false,
                ['Content-Type' => GoPay::JSON, 'Authorization' => ['user', 'pass']],
                ['key' => 'value'],
                $this->buildRequest(
                    Method::POST,
                    'https://gw.sandbox.gopay.com/api/',
                    'Basic dXNlcjpwYXNz',
                    '{"key":"value"}'
                )
            ]
        ];
    }

    private function buildRequest($method, $baseUrl, $auth, $body = '')
    {
        $r = new Request("{$baseUrl}{$this->urlPath}");
        $r->method = $method;
        $r->headers = ['Authorization' => $auth];
        $r->body = $body;
        return $r;
    }
}
