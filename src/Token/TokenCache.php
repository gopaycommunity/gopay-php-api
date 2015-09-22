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
        return $this->loadToken()->isExpired();
    }

    public function getAccessToken()
    {
        return $this->isExpired() ? new AccessToken : $this->loadToken();
    }

    abstract public function setAccessToken(AccessToken $t);

    /** @return \GoPay\Token\AccessToken */
    abstract protected function loadToken();
}
