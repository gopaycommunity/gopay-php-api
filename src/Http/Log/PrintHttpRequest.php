<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;

class PrintHttpRequest implements Logger
{
    /**
     * @return void
     */
    public function logHttpCommunication(Request $request, Response $response)
    {
        $msg = "{$request->method} {$request->url} -> {$response->statusCode}";
        $this->log($msg);
    }

    /**
     * @return void
     */
    public function log(string $message)
    {
        echo "$message\n";
    }
}
