<?php

namespace GoPay\Tests\Unit;

use GoPay\Http\JsonBrowser;
use GoPay\Http\Log\NullLogger;
use GoPay\Http\Request;
use GoPay\Http\Response;

/**
 * JsonBrowser replacement for unit tests: records sent requests and returns
 * queued responses instead of calling the network.
 */
class RecordingBrowser extends JsonBrowser
{
    /** @var list<Request> */
    public array $requests = [];
    /** @var list<Response> */
    private array $responses;

    public function __construct(Response ...$responses)
    {
        parent::__construct(new NullLogger(), 30);
        $this->responses = array_values($responses);
    }

    /**
     * Returns the next queued response, or an empty successful response when the queue is empty.
     */
    public function send(Request $r): Response
    {
        $this->requests[] = $r;
        return array_shift($this->responses) ?? self::response(200, []);
    }

    public function lastRequest(): Request
    {
        $last = end($this->requests);
        if ($last === false) {
            throw new \LogicException('No request has been sent');
        }
        return $last;
    }

    /**
     * Builds a response the same way JsonBrowser does: numeric string status on success, int on error.
     *
     * @param array<mixed> $json
     */
    public static function response(int $statusCode, array $json): Response
    {
        $response = new Response((string) json_encode($json));
        $response->statusCode = $statusCode < 400 ? (string) $statusCode : $statusCode;
        $response->json = $json;
        return $response;
    }

    public static function tokenResponse(string $token = 'test-token', int $expiresIn = 1800): Response
    {
        return self::response(200, [
            'token_type' => 'bearer',
            'access_token' => $token,
            'expires_in' => $expiresIn,
        ]);
    }
}
