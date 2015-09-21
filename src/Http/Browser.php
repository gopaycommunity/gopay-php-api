<?php

namespace GoPay\Http;

use Unirest\Request;
use Unirest\Method;

class Browser
{
    const JSON = 'application/json';
    const FORM = 'application/x-www-form-urlencoded';

    public function setBaseUrl($isProductionMode)
    {
    }

    public function postJson($url, array $headers, array $data)
    {
    }

    public function getJson($url, array $headers)
    {
        return $this->send(Method::GET, $url, $headers);
    }

    private function send($method, $url, $headers = array(), $body = null)
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
