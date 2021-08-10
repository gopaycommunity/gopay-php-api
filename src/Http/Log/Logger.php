<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;

interface Logger
{
    public function logHttpCommunication(Request $request, Response $response);

    public function log(string $message);
}
