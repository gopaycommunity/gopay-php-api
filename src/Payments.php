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

    public function authorize($scope)
    {
        $this->browser->getOAuthToken(
            'https://gw.sandbox.gopay.com/api/oauth2/token',
            "grant_type=client_credentials&scope={$scope}",
            ['auth' => [$this->config['clientID'], $this->config['clientSecret']]]
        );
    }
}
