<?php

namespace GoPay\Token;

/** $client unique identifier of current client (client, environment, scope) */
interface TokenCache
{
    public function setAccessToken($client, AccessToken $t);

    public function getAccessToken($client);
}
