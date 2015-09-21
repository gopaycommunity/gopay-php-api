<?php

namespace GoPay;

class Payments
{
    private $config;
    private $browser;

    public function __construct(array $config, Browser $b)
    {
        $this->config = $config;
        $this->browser = $b;
    }

    public function authorize()
    {
        $this->browser->getOAuthToken(
            'https://gw.sandbox.gopay.com/api/oauth2/token',
            'grant_type=client_credentials&scope=payment-create',
            ['auth' => [$this->config['clientID'], $this->config['clientSecret']]]
        );
    }
}
