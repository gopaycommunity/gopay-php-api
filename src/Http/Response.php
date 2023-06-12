<?php

namespace GoPay\Http;

class Response
{
    /** @var string */
    public $rawBody;
    /** @var int */
    public $statusCode;
    /** @var array response json */
    public $json;

    public function __construct($rawBody = '')
    {
        $this->rawBody = (string) $rawBody;
    }

    public function hasSucceed()
    {
        return $this->statusCode < 400;
    }

    public function __toString()
    {
        return $this->rawBody;
    }
}
