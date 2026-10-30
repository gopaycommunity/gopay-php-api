<?php

namespace GoPay\Http\Log;

use GoPay\Http\Request;
use GoPay\Http\Response;

interface Logger
{
    /**
     * @return void
     */
    public function logHttpCommunication(Request $request, Response $response);

    /**
     * @return void
     */
    public function log(string $message);
}
