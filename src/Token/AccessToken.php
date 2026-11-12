<?php

namespace GoPay\Token;

class AccessToken
{
    /** @var \DateTime|null */
    public $expirationDate;
    /** @var string */
    public $token = '';
    /** @var \GoPay\Http\Response|null response of the token request, set by OAuth2 */
    public $response;

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->token == '' || $this->expirationDate < (new \DateTime());
    }
}
