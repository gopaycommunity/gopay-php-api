<?php

namespace GoPay\Http;

use GoPay\Definition\RequestMethods;

class Request
{
    /** @var RequestMethods */
    public $method = RequestMethods::GET;
    /** @var string */
    public $url;
    /** @var string[] */
    public $headers = [];
    /** @var string */
    public $body = null;

    public function __construct($url)
    {
        $this->url = $url;
    }
}
