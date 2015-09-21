<?php

namespace GoPay\Token;

interface TokenCache
{
    public function setScope($scope);

    public function isExpired();

    public function getAccessToken();

    public function setAccessToken($token, \DateTime $expirationDate);
}
