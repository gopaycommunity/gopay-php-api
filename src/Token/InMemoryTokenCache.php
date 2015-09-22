<?php

namespace GoPay\Token;

class InMemoryTokenCache implements TokenCache
{
    /** @var TokenScope */
    private $scope;
    /** @var AccessToken[] */
    private $tokens;

    public function __construct()
    {
        $this->tokens = [
            TokenScope::ALL => new AccessToken(),
            TokenScope::CREATE_PAYMENT => new AccessToken()
        ];
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function isExpired()
    {
        return $this->tokens[$this->scope]->isExpired();
    }

    public function getAccessToken()
    {
        return $this->isExpired() ? '' : reset($this->tokens[$this->scope]);
    }

    public function setAccessToken($token, \DateTime $expirationDate)
    {
        $this->tokens[$this->scope]->setToken($token, $expirationDate);
    }
}
