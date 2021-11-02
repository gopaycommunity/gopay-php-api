
# Changelog

## v1.4.6
- Added `_5048` substate 

## v1.4.5
- `isProductionMode` is now deprecated and will be removed
- `gatewayUrl` parameter is preferred way to set gateway url
- `Supercash` payment method is no longer supported by the payment gateways, relevant code marked as deprecated

## v1.4.4

 - Dropped support for Guzzle 5 depedency
 - Added Guzzle 7 support


## v1.4.3

- `isProductionMode` may now take anything that can be resolved as a boolean via filter_var ("yes", "true", true ...)  

## v1.2.0

* Added [EET](https://help.gopay.com/cs/tema/propojeni-do-eet/jak-bude-fungovat-napojeni-gopay-do-eet) Support 

## v1.1.1

* Fix `GoPay\Definition\Payment\BankSwiftCode::KOMERCNI_BANKA`
* Composer `autoload-dev`
* Update badges in readme (#2)

## v1.1.0

* Add travis-ci
* Simplified token caching, based on [python-sdk](https://github.com/gopaycommunity/gopay-python-api/)
    * `TokenCache` handles only set/get token, no `isExpired` method
    * `$client` is passed as first argument instead of `setClient` method
    * `getAccessToken` can return null if token does not exist

#### Before

```php
<?php

use GoPay\Token\TokenCache;
use GoPay\Token\AccessToken;

class PrimitiveFileCache extends TokenCache
{
    private $file;

    public function setClient($client)
    {
        $this->file = __DIR__ . "/{$client}";
    }

    public function setAccessToken(AccessToken $t)
    {
        file_put_contents($this->file, serialize($t);
    }

    public function getAccessToken()
    {
        if (file_exists($this->file)) {
            return unserialize(file_get_contents($this->file));
        }
        return $this->getExpiredToken(); 
    }
}
```

#### After

```php
<?php

use GoPay\Token\TokenCache;
use GoPay\Token\AccessToken;

class PrimitiveFileCache implements TokenCache
{
    private $file;

    private function setClient($client)
    {
        $this->file = __DIR__ . "/{$client}";
    }

    public function setAccessToken($client, AccessToken $t)
    {
        $this->setClient($client);
        file_put_contents($this->file, serialize($t);
    }

    public function getAccessToken($client)
    {
        $this->setClient($client);
        if (file_exists($this->file)) {
            return unserialize(file_get_contents($this->file));
        }
        return null;
    }
}
```

## v1.0.1

* Add phpunit's bootstrap (#1)

## v1.0.0

* Call every API method without validation
* Cache access token
* Log HTTP communication
