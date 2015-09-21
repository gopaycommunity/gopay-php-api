<?php

namespace GoPay\Http;

use Unirest\Request;
use Unirest\Method;

class Browser
{
    const JSON = 'application/json';
    const FORM = 'application/x-www-form-urlencoded';

    private $helper;
    private $baseUrl = '';

    public function __construct()
    {
        $this->helper = new RequestHelper();
    }

    public function setBaseUrl($isProductionMode)
    {
        $this->baseUrl = $this->helper->getBaseApiUrl($isProductionMode);
    }

    public function postJson($url, array $headers, array $data)
    {
        $encodedData = $this->helper->encodeData($headers, $data);
        return $this->send(Method::POST, $url, $headers, $encodedData);
    }

    public function getJson($url, array $headers)
    {
        return $this->send(Method::GET, $url, $headers);
    }

    private function send($method, $url, $headers = array(), $body = null)
    {
        try {
            $http = Request::{$method}(
                "{$this->baseUrl}{$url}",
                $this->helper->normalizeHeaders($headers),
                $body
            );
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
