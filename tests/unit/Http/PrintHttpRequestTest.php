<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertStringContainsString;
class PrintHttpRequestTest extends TestCase
{
    public function testShouldPrintInformationAboutCommunication()
    {
        $request = new Request('irrelevant url');
        $response = new Response;
        $response->statusCode = 200;
        $log = $this->getLog($request, $response);

        assertStringContainsString($request->method, $log);
        assertStringContainsString($request->url, $log);
        assertStringContainsString($response->statusCode, $log);

    }

    private function getLog($request, $response)
    {
        ob_start();
        $logger = new PrintHttpRequest;
        $logger->logHttpCommunication($request, $response);
        return ob_get_clean();
    }
}
