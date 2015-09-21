<?php

namespace GoPay\Token;

class InMemoryTokenCache implements TokenCache
{
    private $scope;
    private $tokens = [
        PaymentScope::ALL => ['', null],
        PaymentScope::CREATE => ['', null]
    ];

    public function setScope($scope)
    {
        $this->scope = $scope == PaymentScope::ALL ? PaymentScope::ALL : PaymentScope::CREATE; // helper for tests :)
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
