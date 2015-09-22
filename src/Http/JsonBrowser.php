<?php

namespace GoPay\Http;

use Unirest\Request as Unirest;

class JsonBrowser
{
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
        return $response;
    }
}
