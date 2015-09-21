<?php

namespace GoPay\Auth;

class InMemoryTokenCache implements TokenCache
{
    private $scope;
    private $tokens = [
        PaymentScope::ALL => ['', null],
        PaymentScope::CREATE => ['', null]
    ];

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function isExpired()
    {
        list($token, $expirationDate) = $this->tokens[$this->scope];
        return $token == '' || $expirationDate < (new \DateTime);
    }

    public function getAccessToken()
    {
        return $this->isExpired() ? '' : reset($this->tokens[$this->scope]);
    }

    public function setAccessToken($token, \DateTime $expirationDate)
    {
        $this->tokens[$this->scope] = [$token, $expirationDate];
    }
}
