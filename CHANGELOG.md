
# Changelog

## Unreleased

- Added PHPStan static analysis (level 8) for SDK sources and tests, run in CI
- Public API typed via PHPDoc only, public signatures are unchanged
- Fixed PHPDoc of `Request::$method` and `Response::$statusCode` to match actual values (no runtime change)
- Added PHP-CS-Fixer (PER-CS 3.0 with PHP 8.1 migration rules), `composer cs-check` and `composer cs-fix` scripts, coding style checked in CI
- Applied coding style to SDK sources, tests and examples (formatting only)
- Composer platform pinned to PHP 8.1, the minimum supported version
- Added CaptainHook pre-commit hook (PHP-CS-Fixer and PHPStan on staged files, `composer validate` on `composer.json` change), installed automatically on `composer install` (`captainhook/captainhook` package, not the PHAR, so no GnuPG is needed, e.g. on Windows)
- CI tests also on PHP 8.3 and 8.4, `actions/checkout` bumped to v6
- CI runs `composer validate`, coding style and PHPStan once in a single code quality job, the PHP matrix runs tests only
- CI tests actually run on Ubuntu, Windows and macOS (the OS matrix was previously ignored)
- Added CLAUDE.md
- Fixed broken tests
- Removed unused `phpspec/prophecy` and `phpspec/prophecy-phpunit` dev dependencies
- Account statement test uses last month instead of a hardcoded date range

## 1.11.1

- Fixed JSON encoding of non-ASCII characters (e.g. Bulgarian item names)
- `QrPaymentTest` disabled
- Bumped `phpunit/phpunit` from 9.3.7 to 9.6.33

## 1.11.0

- Added QR payment feature

## 1.10.4

- Added bank swifts `REVOLUT_LT`, `BANK365_SK`

## 1.10.3

- Added bank swift `PTBNCZPP`

## 1.10.2

- Added QR payment swift

## 1.10.1

- Added default `User-Agent` with SDK version

## 1.10.0

- Added `Config` object

## 1.9.0

- Added BNPL payment method
- Added BNPL types constants

## 1.8.0

- Added history of refunds
- Fixed broken tests

## v1.7.3

- Fixed #60

## v1.7.2

- Fixed #52, #58, #59

## v1.7.1

- Removed `isProductionMode`
- Dropped PHP 8.0 support
- Added support for DELETE requests
- Added Card ID support, improved tests
- Added new statement formats
- Updated composer dependencies
- Updated README

## v1.7.0

- Same as v1.6.2

## v1.6.2

- Fixed building URLs for various `gatewayUrl` values
- Updated SWIFT enum

## v1.6.1

- Fixed typo in languages

## v1.6.0

- Removed `HRK` currency, updated languages

## v1.5.7

- Added `_5050` substate

## v1.5.6

- Added `_5049` substate
- Bumped `guzzlehttp/guzzle` from 7.3.0 to 7.4.5

## v1.5.5

- Removed `ERA` bank swift

## v1.5.4

- Added `UK` language constant

## v1.5.3

- `Response::$json` type changed to array
- Dependency update (`guzzlehttp/psr7` 2.2.1)

## v1.5.2

- Fixed missing `$this`

## v1.5.1

- Added `CLICK_TO_PAY` payment instrument

## v1.5.0

- Fixed #44
- Fixed typo in VUB swift constant
- Removed outdated tests
- Updated dependencies

## v1.4.7

- Fix for PHP 8.1
- Changed URL generation
- Examples no longer use deprecated `isProductionMode`
- Modern JS in `js-init` example

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

- Added [EET](https://help.gopay.com/cs/tema/propojeni-do-eet/jak-bude-fungovat-napojeni-gopay-do-eet) Support

## v1.1.1

- Fix `GoPay\Definition\Payment\BankSwiftCode::KOMERCNI_BANKA`
- Composer `autoload-dev`
- Update badges in readme (#2)

## v1.1.0

- Add travis-ci
- Simplified token caching, based on [python-sdk](https://github.com/gopaycommunity/gopay-python-api/)
  - `TokenCache` handles only set/get token, no `isExpired` method
  - `$client` is passed as first argument instead of `setClient` method
  - `getAccessToken` can return null if token does not exist

### Before

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

### After

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

- Add phpunit's bootstrap (#1)

## v1.0.0

- Call every API method without validation
- Cache access token
- Log HTTP communication
