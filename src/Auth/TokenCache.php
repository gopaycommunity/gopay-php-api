<?php

namespace GoPay\Auth;

interface TokenCache
{
    public function isExpired();

    public function getAccessToken();

    public function setAccessToken($token, \DateTime $expirationDate);
}
