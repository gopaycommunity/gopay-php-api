<?php

namespace GoPay\Token;

abstract class TokenCache
{
    /** @var TokenScope */
    protected $scope;

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function isExpired()
    {
        $token = $this->getAccessToken();
        return !($token instanceof AccessToken) || $token->isExpired();
    }

    abstract public function setAccessToken(AccessToken $t);

    abstract public function getAccessToken();
}
