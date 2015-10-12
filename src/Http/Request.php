<?php

namespace GoPay\Http;

use Unirest\Method;

class Request
{
    /** @var Method */
    public $method = Method::GET;
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
