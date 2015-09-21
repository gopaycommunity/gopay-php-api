<?php

namespace GoPay\Http;

class RequestHelperTest extends \PHPUnit_Framework_TestCase
{
    private $request;

    protected function setUp()
    {
        $this->request = new RequestHelper();
    }

    /** @dataProvider provideMode */
    public function testBaseUrlIsDeterminedFromMode($isProductionMode, $expectedUrl)
    {
        assertThat($this->request->getBaseApiUrl($isProductionMode), is($expectedUrl));
    }

    public function provideMode()
    {
        return [
            'test' => [false, 'https://gw.sandbox.gopay.com/api/'],
            'prod' => [true, 'https://gate.gopay.cz/api/'],
        ];
    }

    /** @dataProvider provideContentTypeAndData */
    public function testShouldEncodeData($contentType, $data, $expectedBody)
    {
        $headers = ['Content-Type' => $contentType];
        assertThat($this->request->encodeData($headers, $data), identicalTo($expectedBody));
    }

    public function provideContentTypeAndData()
    {
        return [
            'empty form' => [Browser::FORM, [], ''],
            'empty json' => [Browser::JSON, [], ''],
            'form' => [Browser::FORM, ['key' => 'value'], 'key=value'],
            'json' => [Browser::JSON, ['key' => 'value'], '{"key":"value"}'],
        ];
    }

    /** @dataProvider provideAuth */
    public function testShouldNormalizeAuthorization($auth, $expectedHeader)
    {
        $headers = ['Authorization' => $auth];
        $normalized = $this->request->normalizeHeaders($headers);
        assertThat($normalized['Authorization'], identicalTo($expectedHeader));
    }

    public function provideAuth()
    {
        return [
            'token' => ['Bearer irrelevantToken', 'Bearer irrelevantToken'],
            'basic auth' => [['user', 'pass'], 'Basic dXNlcjpwYXNz'],
        ];
    }
}
