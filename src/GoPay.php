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

    const LOCALE_CZECH = 'cs-CZ';
    const LOCALE_ENGLISH = 'en-US';

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
        $r = new Request($this->buildUrl("api/{$urlPath}"));
        $r->method = is_array($data) ? Method::POST : Method::GET;
        $r->headers = $this->buildHeaders($contentType, $authorization);
        $r->body = $this->encodeData($contentType, $data);
        return $this->browser->send($r);
    }

    public function buildUrl($urlPath)
    {
        static $urls = [
            true => 'https://gate.gopay.cz/',
            false => 'http://gopay-gw:8180/gp/'
        ];
        return $urls[(bool) $this->getConfig('isProductionMode')] . $urlPath;
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
            'Authorization' => $authorization
        ];
    }

    private function getAcceptedLanguage()
    {
        static $czechLike = [Language::CZECH, Language::SLOVAK];
        return in_array($this->getConfig('language'), $czechLike) ? self::LOCALE_CZECH : self::LOCALE_ENGLISH;
    }
}
