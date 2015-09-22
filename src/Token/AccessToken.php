<?php

namespace GoPay\Token;

class AccessToken
{
    /** @var \DateTime */
    public $expirationDate;
    /** @var string */
    public $token = '';
    /** @var \GoPay\Http\Response */
    public $response;

    public function isExpired()
    {
        return $this->token == '' || $this->expirationDate < (new \DateTime);
    }
}
