<?php

namespace GoPay\Http;

use Unirest\Request;

class Browser
{
    public function send($method, $url, $headers = array(), $body = null)
    {
        try {
            $http = Request::{$method}($url, $headers, $body);
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
