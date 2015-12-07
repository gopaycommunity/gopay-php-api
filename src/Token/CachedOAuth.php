<?php

namespace GoPay\Token;

class CachedOAuth
{
    private $oauth;
    private $cache;

    public function __construct(\GoPay\OAuth2 $auth, TokenCache $cache)
    {
        $this->oauth = $auth;
        $this->cache = $cache;
    }

    public function authorize()
    {
        $client = $this->oauth->getClient();
        $token = $this->cache->getAccessToken();
        if (!($token instanceof AccessToken) || $token->isExpired()) {
            $token = $this->oauth->authorize();
            $this->cache->setAccessToken($token);
        }
        return $token;
    }
}
