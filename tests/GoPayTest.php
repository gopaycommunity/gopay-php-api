<?php

namespace GoPay;

use Unirest\Method;
use GoPay\Http\Request;
use GoPay\Definition\Language;
use Prophecy\Argument;

class GoPayTest extends \PHPUnit_Framework_TestCase
{
    private $urlPath = 'irrelevant-path';
    private $browser;

    protected function setUp()
    {
        $this->browser = $this->prophesize('GoPay\Http\JsonBrowser');
    }

    /** @dataProvider provideRequest */
    public function testShouldCompleteRequest($isProductionMode, $headers, $body, Request $expectedRequest)
    {
        $expectedRequest->headers = array_merge(
            $headers,
            $expectedRequest->headers,
            ['Accept-Language' => Language::LOCALE_CZECH]
        );
        $this->browser->send($expectedRequest)->shouldBeCalled();
        $this->givenGopay(Language::CZECH, $isProductionMode)->call($this->urlPath, $headers, $body);
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

    /** @dataProvider provideLanguage */
    public function testShouldLocalizeErrorMessages($language, $expectedLanguage)
    {
        $this->browser->send(Argument::that(function (Request $r) use ($expectedLanguage) {
            assertThat($r->headers['Accept-Language'], is($expectedLanguage));
            return true;
        }))->shouldBeCalled();
        $this->givenGopay($language)->call($this->urlPath, []);
    }

    public function provideLanguage()
    {
        $languages = [
            Language::LOCALE_CZECH => [Language::CZECH, Language::SLOVAK],
            Language::LOCALE_ENGLISH => ['', Language::ENGLISH, Language::RUSSIAN, 'unknown language'],
        ];
        $params = [];
        foreach ($languages as $locale => $langs) {
            foreach ($langs as $lang) {
                $params[] = [$lang, $locale];
            }
        }
        return $params;
    }

    private function buildRequest($method, $baseUrl, $auth, $body = '')
    {
        $r = new Request("{$baseUrl}{$this->urlPath}");
        $r->method = $method;
        $r->headers = ['Authorization' => $auth];
        $r->body = $body;
        return $r;
    }
    private function givenGopay($language, $isProduction = false)
    {
        return new GoPay(['isProductionMode' => $isProduction, 'language' => $language], $this->browser->reveal());
    }
}
