<?php

namespace GoPay\Auth;

class InMemoryTokenCache implements TokenCache
{
    private $accessToken = '';
    private $expirationDate;

    public function isExpired()
    {
        return $this->accessToken == '' || $this->expirationDate < (new \DateTime);
    }

    public function getAccessToken()
    {
        return $this->isExpired() ? '' : $this->accessToken;
    }

    public function setAccessToken($token, \DateTime $expirationDate)
    {
        $this->accessToken = $token;
        $this->expirationDate = $expirationDate;
    }
}
