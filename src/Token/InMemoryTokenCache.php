<?php

namespace GoPay\Token;

class InMemoryTokenCache extends TokenCache
{
    /** @var AccessToken[] */
    private $tokens;

    public function __construct()
    {
        $this->tokens = [
            TokenScope::ALL => new AccessToken(),
            TokenScope::CREATE_PAYMENT => new AccessToken()
        ];
    }

    public function setAccessToken(AccessToken $t)
    {
        $this->tokens[$this->scope] = $t;
    }

    public function getAccessToken()
    {
        return $this->tokens[$this->scope];
    }
}
