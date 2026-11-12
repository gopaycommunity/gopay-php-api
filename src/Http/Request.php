<?php

namespace GoPay\Http;

use GoPay\Definition\RequestMethods;

class Request
{
    /** @var string one of RequestMethods constants */
    public $method = RequestMethods::GET;
    /** @var string */
    public $url;
    /** @var array<string, string> */
    public $headers = [];
    /** @var string|false|null false when JSON encoding of the data fails */
    public $body = null;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }
}
