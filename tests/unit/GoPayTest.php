<?php

namespace GoPay;

use GoPay\Definition\RequestMethods;
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
    public function testShouldBuildRequest($isProductionMode, $contentType, $auth, $body, Request $expectedRequest)
    {
        $expectedRequest->headers = $expectedRequest->headers + [
            'Accept' => 'application/json',
            'Content-Type' => $contentType,
            'Accept-Language' => GoPay::LOCALE_CZECH
        ];
        $this->browser->send($expectedRequest)->shouldBeCalled();
        $this->givenGoPay(Language::CZECH, $isProductionMode)->call($this->urlPath, $contentType, $auth, $expectedRequest->method, $body);
    }

    /** @dataProvider provideRequest */
    public function testShouldBuildRequestWithDifferentArgSeparator($isProductionMode, $contentType, $auth, $body, Request $expectedRequest)
    {
        $this->iniSet('arg_separator.output', '&amp;');
        $expectedRequest->headers = $expectedRequest->headers + [
                'Accept' => 'application/json',
                'Content-Type' => $contentType,
                'Accept-Language' => GoPay::LOCALE_CZECH
            ];
        $this->browser->send($expectedRequest)->shouldBeCalled();
        $this->givenGoPay(Language::CZECH, $isProductionMode)->call($this->urlPath, $contentType, $auth, $expectedRequest->method, $body);
    }

    public function provideRequest()
    {
        return [
            'get form in production' => [
                true,
                GoPay::FORM,
                'Bearer irrelevantToken',
                null,
                $this->buildRequest(
                    RequestMethods::GET,
                    'https://gate.gopay.cz/api/',
                    'Bearer irrelevantToken'
                )
            ],
            'send form in production' => [
                true,
                GoPay::FORM,
                'Bearer irrelevantToken',
                ['key' => 'value'],
                $this->buildRequest(
                    RequestMethods::POST,
                    'https://gate.gopay.cz/api/',
                    'Bearer irrelevantToken',
                    'key=value'
                )
            ],
            'send form with more arguments' => [
                false,
                GoPay::FORM,
                'Basic irrelevantCode',
                [
                    'grant_type' => 'client_credentials',
                    'scope' => 'payment-all',
                ],
                $this->buildRequest(
                    RequestMethods::POST,
                    'https://gw.sandbox.gopay.com/api/',
                    'Basic irrelevantCode',
                    'grant_type=client_credentials&scope=payment-all'
                )
            ],
            'send json in test' => [
                false,
                GoPay::JSON,
                'Basic irrelevantCode',
                ['key' => 'value'],
                $this->buildRequest(
                    RequestMethods::POST,
                    'https://gw.sandbox.gopay.com/api/',
                    'Basic irrelevantCode',
                    '{"key":"value"}'
                )
            ],
            'send json in test with more arguments' => [
                false,
                GoPay::JSON,
                'Basic irrelevantCode',
                ['key' => 'value', 'key2' => 'value2'],
                $this->buildRequest(
                    RequestMethods::POST,
                    'https://gw.sandbox.gopay.com/api/',
                    'Basic irrelevantCode',
                    '{"key":"value","key2":"value2"}'
                )
            ]
        ];
    }

    /** @dataProvider provideLanguage */
    public function testShouldLocalizeErrorMessage($language, $expectedLanguage)
    {
        $this->browser->send(Argument::that(function (Request $r) use ($expectedLanguage) {
            assertThat($r->headers['Accept-Language'], is($expectedLanguage));
            return true;
        }))->shouldBeCalled();
        $this->givenGoPay($language)->call($this->urlPath, 'irrelevant content-type', 'irrelevant auth', RequestMethods::POST);
    }

    public function provideLanguage()
    {
        $languages = [
            GoPay::LOCALE_CZECH => [Language::CZECH, Language::SLOVAK],
            GoPay::LOCALE_ENGLISH => ['', Language::ENGLISH, Language::RUSSIAN, 'unknown language'],
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

    private function givenGoPay($language, $isProduction = false)
    {
        return new GoPay(['isProductionMode' => $isProduction, 'language' => $language], $this->browser->reveal());
    }
}
