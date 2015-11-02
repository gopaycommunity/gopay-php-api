<?php

namespace GoPay\Token;

class InMemoryTokenCache extends TokenCache
{
    /** @var AccessToken[] */
    private $tokens = [];

    public function setAccessToken(AccessToken $t)
    {
        $this->tokens[$this->client] = $t;
    }

    public function getAccessToken()
    {
        return array_key_exists($this->client, $this->tokens) ? $this->tokens[$this->client] : $this->getExpiredToken();
    }
}
