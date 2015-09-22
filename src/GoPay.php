<?php

namespace GoPay;

use Unirest\Method;
use GoPay\Http\Request;
use GoPay\Http\JsonBrowser;

class GoPay
{
    const JSON = 'application/json';
    const FORM = 'application/x-www-form-urlencoded';

    private $config;
    private $browser;

    public function __construct(array $config, JsonBrowser $b)
    {
        $this->config = $config;
        $this->browser = $b;
    }

    public function getConfig($key)
    {
        return $this->config[$key];
    }

    public function call($urlPath, array $headers, $data = null)
    {
        $r = new Request("{$this->getBaseApiUrl()}{$urlPath}");
        $r->method = is_array($data) ? Method::POST : Method::GET;
        $r->headers = $this->normalizeHeaders($headers);
        $r->body = $this->encodeData($headers, $data);
        return $this->browser->send($r);
    }

    private function getBaseApiUrl()
    {
        static $urls = [
            true => 'https://gate.gopay.cz/api/',
            false => 'https://gw.sandbox.gopay.com/api/'
        ];
        return $urls[(bool) $this->getConfig('isProductionMode')];
    }

    private function encodeData(array $headers, $data)
    {
        if ($data) {
            $encoder = $headers['Content-Type'] == GoPay::FORM ? 'http_build_query' : 'json_encode';
            return $encoder($data);
        }
        return '';
    }

    private function normalizeHeaders(array $headers)
    {
        if (array_key_exists('Authorization', $headers) && is_array($headers['Authorization'])) {
            $credentials = implode(':', $headers['Authorization']);
            $headers['Authorization'] = 'Basic ' . base64_encode($credentials);
        }
        return $headers;
    }
}
