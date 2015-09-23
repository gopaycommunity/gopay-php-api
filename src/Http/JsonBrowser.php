<?php

namespace GoPay\Http;

use Unirest\Request as Unirest;
use GoPay\Http\Log\Logger;

class JsonBrowser
{
    private $logger;

    public function __construct(Logger $l)
    {
        $this->logger = $l;
    }

    public function send(Request $r)
    {
        try {
            $http = Unirest::{$r->method}($r->url, $r->headers, $r->body);
            $response = new Response((string) $http->raw_body);
            $response->statusCode = (string) $http->code;
            $response->json = json_decode((string) $response, true);
        } catch (\Exception $e) {
            $response = new Response($e->getMessage());
            $response->statusCode = 500;
        }
        $this->logger->logHttpCommunication($r, $response);
        return $response;
    }
}
