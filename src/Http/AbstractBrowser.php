<?php

namespace GoPay\Http;

interface AbstractBrowser
{
    public function send(Request $r);
}