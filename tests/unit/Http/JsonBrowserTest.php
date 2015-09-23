<?php

namespace GoPay\Http;

use Prophecy\Argument;

class JsonBrowserTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider provideJson */
    public function testShouldExecuteHttpRequestAndAlwaysReturnResponse($url, $hasSucceed, $expectedJson)
    {
        $logger = $this->prophesize('GoPay\Http\Log\Logger');
        $logger->logHttpCommunication(Argument::cetera())->shouldBeCalled();

        $browser = new JsonBrowser($logger->reveal());
        $response = $browser->send(new Request($url));
        assertThat($response->hasSucceed(), is($hasSucceed));
        assertThat((string) $response, is(nonEmptyString()));
        assertThat($response->json, is($expectedJson));
    }

    public function provideJson()
    {
        return array(
            'existing json' => array('http://www.rozpisyzapasu.cz/api/', true, nonEmptyArray()),
            'non existent page' => array('http://www.non-existent-page.cz/', false, emptyArray())
        );
    }
}
