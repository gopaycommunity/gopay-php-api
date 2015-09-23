<?php

namespace GoPay\Http;

interface Logger
{
    public function logHttpCommunication(Request $request, Response $response);
}
