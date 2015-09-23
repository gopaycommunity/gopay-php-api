<?php

namespace GoPay\Token;

class InMemoryTokenCache extends TokenCache
{
    /** @var AccessToken[] */
    private $tokens = [];

    public function setAccessToken(AccessToken $t)
    {
        $this->tokens[$this->scope] = $t;
    }

    public function getAccessToken()
    {
        return array_key_exists($this->scope, $this->tokens) ? $this->tokens[$this->scope] : new AccessToken;
    }
}
