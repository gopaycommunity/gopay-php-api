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
        return $this->getAccessToken()->isExpired();
    }

    abstract public function setAccessToken(AccessToken $t);

    abstract public function getAccessToken();
}
