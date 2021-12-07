<?php

namespace GoPay;

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
        if (array_key_exists('isProductionMode', $this->config)) {
            $this->browser->getLogger()->log("isProductionMode is deprecated and will be removed, please use gatewayUrl instead");
        }
    }

    public function getConfig($key)
    {
        return $this->config[$key];
    }

    public function call($urlPath, $contentType, $authorization, $method, $data = null)
    {
        $r = new Request($this->buildUrl("/{$urlPath}"));
        $r->method = $method;
        $r->headers = $this->buildHeaders($contentType, $authorization);
        $r->body = $this->encodeData($contentType, $data);
        return $this->browser->send($r);
    }

    public function buildUrl($urlPath)
    {
        static $urls = [
            true => 'https://gate.gopay.cz/api',
            false => 'https://gw.sandbox.gopay.com/api'
        ];

        if ($this->isCustomGatewayUrl()) {
            $apiRoot = $this->config['gatewayUrl'];
            if (!str_ends_with($apiRoot, 'api')) {
                $apiRoot = $apiRoot . 'api';
            }
            return $apiRoot . $urlPath;
        }

        return $urls[$this->isProductionMode()] . $urlPath;
    }

    public function buildEmbedUrl()
    {
        static $urls = [
            true => 'https://gate.gopay.cz/',
            false => 'https://gw.sandbox.gopay.com/'
        ];

        if ($this->isCustomGatewayUrl()) {
            $urlBase = $this->config['gatewayUrl'];
            if (str_ends_with($urlBase, 'api')) {
                $urlBase = substr($urlBase, 0, -3);
            }
            return $urlBase . 'gp-gw/js/embed.js';
        }

        return $urls[$this->isProductionMode()] . 'gp-gw/js/embed.js';
    }


    public function isCustomGatewayUrl()
    {
        return array_key_exists('gatewayUrl', $this->config);
    }

    /**
     * @deprecated use gatewayUrl
     */
    public function isProductionMode()
    {
        $productionMode = $this->getConfig('isProductionMode');
        return filter_var($productionMode, FILTER_VALIDATE_BOOLEAN);
    }

    private function encodeData($contentType, $data)
    {
        if ($data) {
            if ($contentType === GoPay::FORM) {
                return http_build_query($data, "", '&');
            }
            return json_encode($data);
        }
        return '';
    }

    private function buildHeaders($contentType, $authorization)
    {
        if (is_null($contentType)) {
            return [
                'Accept' => 'application/json',
                'Accept-Language' => $this->getAcceptedLanguage(),
                'Authorization' => $authorization
            ];
        } else {
            return [
                'Accept' => 'application/json',
                'Accept-Language' => $this->getAcceptedLanguage(),
                'Content-Type' => $contentType,
                'Authorization' => $authorization
            ];
        }
    }

    private function getAcceptedLanguage()
    {
        static $czechLike = [Language::CZECH, Language::SLOVAK];
        return in_array($this->getConfig('language'), $czechLike) ? self::LOCALE_CZECH : self::LOCALE_ENGLISH;
    }
}
