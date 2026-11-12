<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;

class NullLogger implements Logger
{
    /**
     * @return void
     */
    public function logHttpCommunication(Request $request, Response $response) {}

    /**
     * @return void
     */
    public function log(string $message) {}
}
