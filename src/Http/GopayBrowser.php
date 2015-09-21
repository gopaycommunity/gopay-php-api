<?php

namespace GoPay\Http;

use GoPay\Http\Browser;

class GopayBrowser
{
    private $config;
    private $browser;

    public function __construct(array $config, Browser $b)
    {
        $this->config = $config;
        $this->browser = $b;
    }

    public function api($urlPath, array $headers, $data = null)
    {
        $method = is_array($data) ? 'postJson' : 'getJson';
        $this->browser->setBaseUrl($this->config['isProductionMode']);
        return $this->browser->{$method}(
            $urlPath,
            $headers,
            $data
        );
    }
}
