<?php

namespace GoPay\Token;

abstract class TokenCache
{
    /** @var string */
    protected $client;

    /** @param string $client unique identifier of current client (client, environment, scope) */
    public function setClient($client)
    {
        $this->client = $client;
    }

    public function isExpired()
    {
        $token = $this->getAccessToken();
        return !($token instanceof AccessToken) || $token->isExpired();
    }

    abstract public function setAccessToken(AccessToken $t);

    abstract public function getAccessToken();
}
