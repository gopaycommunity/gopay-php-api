<?php

namespace GoPay\Http;

class Response
{
    /** @var int */
    public $statusCode;
    /** @var array */
    public $json;

    public function hasSucceed()
    {
        return $this->statusCode == 200;
    }
}
