<?php

namespace GoPay\Token;

class InMemoryTokenCache implements TokenCache
{
    /** @var AccessToken[] */
    private $tokens = [];

    public function setAccessToken($client, AccessToken $t)
    {
        $this->tokens[$client] = $t;
    }

    public function getAccessToken($client)
    {
        return array_key_exists($client, $this->tokens) ? $this->tokens[$client] : null;
    }
}
