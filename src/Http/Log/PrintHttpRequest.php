<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;

class PrintHttpRequest implements Logger
{
    public function logHttpCommunication(Request $request, Response $response)
    {
        echo "{$request->method} {$request->url} -> {$response->statusCode}\n";
    }
}
