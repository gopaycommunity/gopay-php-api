<?php

namespace GoPay\Http;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophet;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;

class JsonBrowserTest extends TestCase
{
    /** @dataProvider provideJson */
    public function testShouldExecuteHttpRequestAndAlwaysReturnResponse($url, $hasSucceed, $expectedJson)
    {
        $mock = $this->getMockBuilder('GoPay\Http\Log\Logger')
                ->onlyMethods(array('logHttpCommunication'))
                ->getMock();
        $mock->expects($this->atLeastOnce())->method('logHttpCommunication');

        $timeout = 5;
        $browser = new JsonBrowser($mock, $timeout);
        $response = $browser->send(new Request($url));
        echo $response;
        assertEquals($response->hasSucceed(), $hasSucceed);
        assertNotEmpty((string) $response);
        assertEquals($response->json, $expectedJson);
    }

    public function provideJson()
    {
        return array(
            'existing json' => array('https://gopay.com/', true, emptyArray()),
            'non existent page' => array('https://gw.sandboxx.gopay.com/api', false, nonEmptyArray())
        );
    }
}
