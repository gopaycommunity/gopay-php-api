<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;

class NullLogger implements Logger
{
    public function logHttpCommunication(Request $request, Response $response)
    {
    }
}
