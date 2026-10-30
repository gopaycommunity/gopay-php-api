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
    /** @var array<mixed>|null decoded response json, null if body is not valid JSON */
    public $json;

    /**
     * @param string|\Stringable $rawBody
     */
    public function __construct($rawBody = '')
    {
        $this->rawBody = (string) $rawBody;
    }

    /**
     * @return bool true if status code is lower than 400
     */
    public function hasSucceed()
    {
        return $this->statusCode < 400;
    }

    public function __toString()
    {
        return $this->rawBody;
    }
}
