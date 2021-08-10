<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;

class PrintHttpRequest implements Logger
{
    public function logHttpCommunication(Request $request, Response $response)
    {
        $msg = "{$request->method} {$request->url} -> {$response->statusCode}";
        $this->log($msg);
    }

    public function log(string $message)
    {
        echo "$message\n";
    }
}
