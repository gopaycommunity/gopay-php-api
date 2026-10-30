<?php

namespace GoPay\Http;

class Response
{
    /** @var string */
    public $rawBody;
    /**
     * HTTP status code. Successful responses store it as numeric string, error responses as int.
     *
     * @var int|string|null
     */
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
