<?php

namespace GoPay\Http;

use Unirest\Method;

class GopayBrowser
{
    const JSON = 'application/json';
    const FORM = 'application/x-www-form-urlencoded';

    private $config;
    private $browser;

    public function __construct(array $config, Browser $b)
    {
        $this->config = $config;
        $this->browser = $b;
    }

    public function api($urlPath, array $headers, $data = null)
    {
        return $this->browser->send(
            is_array($data) ? Method::POST : Method::GET,
            "{$this->getBaseApiUrl()}{$urlPath}",
            $this->normalizeHeaders($headers),
            $this->encodeData($headers, $data)
        );
    }

    private function getBaseApiUrl()
    {
        static $urls = [
            true => 'https://gate.gopay.cz/api/',
            false => 'https://gw.sandbox.gopay.com/api/'
        ];
        return $urls[(bool) $this->config['isProductionMode']];
    }

    private function encodeData(array $headers, $data)
    {
        if ($data) {
            $encoder = $headers['Content-Type'] == GopayBrowser::FORM ? 'http_build_query' : 'json_encode';
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
