<?php

namespace GoPay\Http;

class Response
{
    /** @var string */
    private $rawBody;
    /** @var int */
    public $statusCode;
    /** @var object response json */
    public $json;

    public function __construct($rawBody = '')
    {
        $this->rawBody = (string) $rawBody;
    }

    public function hasSucceed()
    {
        return $this->statusCode == 200;
    }

    public function __toString()
    {
        return $this->rawBody;
    }
}
