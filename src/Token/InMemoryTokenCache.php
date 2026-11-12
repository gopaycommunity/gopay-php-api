<?php

namespace GoPay\Token;

class InMemoryTokenCache implements TokenCache
{
    /** @var array<string, AccessToken> */
    private array $tokens = [];

    /**
     * @param string $client
     *
     * @return void
     */
    public function setAccessToken($client, AccessToken $t)
    {
        $this->tokens[$client] = $t;
    }

    /**
     * @param string $client
     *
     * @return AccessToken|null
     */
    public function getAccessToken($client)
    {
        return array_key_exists($client, $this->tokens) ? $this->tokens[$client] : null;
    }
}
