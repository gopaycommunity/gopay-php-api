<?php

namespace GoPay;

use Unirest\Method;
use GoPay\Http\Request;
use GoPay\Http\JsonBrowser;
use GoPay\Definition\Language;

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

    public function call($urlPath, $contentType, $authorization, $data = null)
    {
        $r = new Request("{$this->getBaseApiUrl()}{$urlPath}");
        $r->method = is_array($data) ? Method::POST : Method::GET;
        $r->headers = $this->buildHeaders($contentType, $authorization);
        $r->body = $this->encodeData($contentType, $data);
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

    private function encodeData($contentType, $data)
    {
        if ($data) {
            $encoder = $contentType == GoPay::FORM ? 'http_build_query' : 'json_encode';
            return $encoder($data);
        }
        return '';
    }

    private function buildHeaders($contentType, $authorization)
    {
        return [
            'Accept' => 'application/json',
            'Accept-Language' => $this->getAcceptedLanguage(),
            'Content-Type' => $contentType,
            'Authorization' => $this->getAuthorization($authorization)
        ];
    }

    private function getAuthorization($authorization)
    {
        if (is_array($authorization)) {
            $credentials = implode(':', $authorization);
            return 'Basic ' . base64_encode($credentials);
        }
        return $authorization;
    }

    private function getAcceptedLanguage()
    {
        $language = $this->getConfig('language');
        return Language::getAcceptedLocale($language);
    }
}
