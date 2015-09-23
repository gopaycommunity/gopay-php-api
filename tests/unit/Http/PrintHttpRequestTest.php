<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;

class PrintHttpRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldPrintInformationAboutCommunication()
    {
        $request = new Request('irrelevant url');
        $response = new Response;
        $response->statusCode = 200;
        assertThat(
            $this->getLog($request, $response),
            allOf(
                containsString($request->method),
                containsString($request->url),
                containsString((string) $response->statusCode)
            )
        );
    }

    private function getLog($request, $response)
    {
        ob_start();
        $logger = new PrintHttpRequest;
        $logger->logHttpCommunication($request, $response);
        return ob_get_clean();
    }
}
