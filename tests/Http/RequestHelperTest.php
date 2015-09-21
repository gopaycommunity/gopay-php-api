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
}
