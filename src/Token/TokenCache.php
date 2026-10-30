<?php

namespace GoPay\Token;

/** $client unique identifier of current client (client, environment, scope) */
interface TokenCache
{
    /**
     * @param string $client
     * @return void
     */
    public function setAccessToken($client, AccessToken $t);

    /**
     * @param string $client
     * @return AccessToken|null
     */
    public function getAccessToken($client);
}
