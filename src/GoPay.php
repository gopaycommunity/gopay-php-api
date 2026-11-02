<?php

namespace GoPay;

use GoPay\Http\Request;
use GoPay\Http\Response;
use GoPay\Http\JsonBrowser;
use GoPay\Definition\Language;

class GoPay
{
    public const JSON = 'application/json';
    public const FORM = 'application/x-www-form-urlencoded';

    public const LOCALE_CZECH = 'cs-CZ';
    public const LOCALE_ENGLISH = 'en-US';

    public const VERSION = '1.11.1';
    public const DEFAULT_USER_AGENT = 'GoPay PHP ' . self::VERSION;

    /** @var array<string, mixed> */
    private array $config;
    private JsonBrowser $browser;

    /**
     * @param array<string, mixed>|Config $config
     */
    public function __construct(array|Config $config, JsonBrowser $b)
    {
        $this->config = Config::parseUserConfig($config, false);
        $this->browser = $b;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->config[$key];
    }

    /**
     * @param string $urlPath
     * @param string $authorization
     * @param string $method one of RequestMethods constants
     * @param string|null $contentType GoPay::JSON, GoPay::FORM or null for requests without body
     * @param array<mixed>|null $data
     * @return Response
     */
    public function call($urlPath, $authorization, $method, $contentType = null, $data = null)
    {
        $r = new Request($this->buildUrl($urlPath));
        $r->method = $method;
        $r->headers = $this->buildHeaders($contentType, $authorization);
        $r->body = $this->encodeData($contentType, $data);
        return $this->browser->send($r);
    }

    /**
     * @param string $urlPath
     * @return string
     */
    public function buildUrl($urlPath)
    {
        $urlBase = rtrim($this->config['gatewayUrl'], '/');
        if (substr($urlBase, -4) !== '/api') {
            $urlBase .= '/api';
        }

        return $urlBase . $urlPath;
    }

    /**
     * @return string
     */
    public function buildEmbedUrl()
    {
        $urlBase = rtrim($this->config['gatewayUrl'], '/');
        if (substr($urlBase, -4) === '/api') {
            $urlBase = substr($urlBase, 0, -4);
        }

        return $urlBase . '/gp-gw/js/embed.js';
    }


    /**
     * @param string|null $contentType
     * @param array<mixed>|null $data
     */
    private function encodeData($contentType, $data): string|false
    {
        if ($data) {
            if ($contentType === GoPay::FORM) {
                return http_build_query($data, "", '&');
            }
            // JSON_UNESCAPED_UNICODE ensures non-ASCII characters (e.g. Cyrillic, Bulgarian)
            // are sent as-is in UTF-8 instead of being \uXXXX-escaped.
            // Escaped sequences were incorrectly flagged as SQL Injection by the gateway WAF,
            // causing 403 Forbidden responses for merchants using non-Latin item names.
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return '';
    }

    /**
     * @param string|null $contentType
     * @param string $authorization
     * @return array<string, string>
     */
    private function buildHeaders($contentType, $authorization): array
    {
        $customUserAgent = array_key_exists('customUserAgent', $this->config) ? $this->config['customUserAgent'] : null;
        if (is_null($contentType)) {
            return [
                'Accept' => 'application/json',
                'Accept-Language' => $this->getAcceptedLanguage(),
                'Authorization' => $authorization,
                'User-Agent' => is_null($customUserAgent) ? self::DEFAULT_USER_AGENT : $customUserAgent
            ];
        } else {
            return [
                'Accept' => 'application/json',
                'Accept-Language' => $this->getAcceptedLanguage(),
                'Content-Type' => $contentType,
                'Authorization' => $authorization,
                'User-Agent' => is_null($customUserAgent) ? self::DEFAULT_USER_AGENT : $customUserAgent
            ];
        }
    }

    private function getAcceptedLanguage(): string
    {
        static $czechLike = [Language::CZECH, Language::SLOVAK];
        return in_array($this->getConfig('language'), $czechLike) ? self::LOCALE_CZECH : self::LOCALE_ENGLISH;
    }
}
