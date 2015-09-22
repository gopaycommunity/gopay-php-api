<?php

namespace GoPay;

use Unirest\Method;
use GoPay\Http\Request;
use GoPay\Definition\Language;
use Prophecy\Argument;

class GoPayTest extends \PHPUnit_Framework_TestCase
{
    private $urlPath = 'irrelevant-path';

    /** @dataProvider provideLanguage */
    public function testShouldLocalizeErrorMessages($language, $expectedLanguage)
    {
        $browser = $this->prophesize('GoPay\Http\JsonBrowser');
        $browser->send(Argument::that(function (Request $r) use ($expectedLanguage) {
            assertThat($r->headers['Accept-Language'], is($expectedLanguage));
            return true;
        }))->shouldBeCalled();

        $gopay = new GoPay(['isProductionMode' => false, 'language' => $language], $browser->reveal());
        $gopay->call($this->urlPath, []);
    }

    public function provideLanguage()
    {
        $languages = [
            'cs-CZ' => [Language::CZECH, Language::SLOVAK],
            'en-US' => ['', Language::ENGLISH, Language::RUSSIAN, 'unknown language'],
        ];
        $params = [];
        foreach ($languages as $locale => $langs) {
            foreach ($langs as $lang) {
                $params[] = [$lang, $locale];
            }
        }
        return $params;
    }

    /** @dataProvider provideRequest */
    public function testShouldCompleteRequest($isProductionMode, $headers, $body, Request $expectedRequest)
    {
        $expectedRequest->headers = array_merge(
            $headers,
            $expectedRequest->headers,
            ['Accept-Language' => 'cs-CZ']
        );

        $browser = $this->prophesize('GoPay\Http\JsonBrowser');
        $browser->send($expectedRequest)->shouldBeCalled();

        $gopay = new GoPay(['isProductionMode' => $isProductionMode, 'language' => Language::CZECH], $browser->reveal());
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
