<?php

namespace GoPay\Tests\Unit;

use GoPay\Http\JsonBrowser;
use GoPay\Http\Log\Logger;
use GoPay\Http\Request;
use GoPay\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * JsonBrowser creates its Guzzle client internally, so only the transport error path
 * can be tested offline. HTTP responses (including ClientException) are covered by
 * the integration tests.
 */
class JsonBrowserTest extends TestCase
{
    public function testTransportErrorIsConvertedToStatus500(): void
    {
        $logger = new class implements Logger {
            /** @var list<array{Request, Response}> */
            public array $logged = [];

            public function logHttpCommunication(Request $request, Response $response)
            {
                $this->logged[] = [$request, $response];
            }

            public function log(string $message) {}
        };
        $browser = new JsonBrowser($logger, 5);
        // Unsupported scheme fails before any connection is made
        $request = new Request('unsupported://localhost/api/payments/payment/123');

        $response = $browser->send($request);

        $this->assertSame(500, $response->statusCode);
        $this->assertFalse($response->hasSucceed());
        $this->assertSame([], $response->json);
        $this->assertStringContainsString('unsupported', $response->rawBody);
        $this->assertCount(1, $logger->logged);
        $this->assertSame($request, $logger->logged[0][0]);
        $this->assertSame($response, $logger->logged[0][1]);
    }

    public function testLoggerIsAccessible(): void
    {
        $logger = new \GoPay\Http\Log\NullLogger();

        $this->assertSame($logger, (new JsonBrowser($logger, 5))->getLogger());
    }
}
