<?php

namespace GoPay\Token;

use GoPay\Auth;
use GoPay\OAuth2;

class CachedOAuth implements Auth
{
    private $oauth;
    private $cache;

    public function __construct(OAuth2 $auth, TokenCache $cache)
    {
        $this->oauth = $auth;
        $this->cache = $cache;
    }

    public function authorize()
    {
        $client = $this->oauth->getClient();
        $token = $this->cache->getAccessToken($client);
        if (!($token instanceof AccessToken) || $token->isExpired()) {
            $token = $this->oauth->authorize();
            $this->cache->setAccessToken($client, $token);
        }
        return $token;
    }
}
