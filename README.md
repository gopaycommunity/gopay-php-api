
# Gopay's PHP SDK for Payments REST API

[![License](https://poser.pugx.org/gopay/payments-php-sdk/license)](https://packagist.org/packages/gopay/payments-php-sdk)
[![Latest Stable Version](https://poser.pugx.org/gopay/payments-php-sdk/v/stable)](https://packagist.org/packages/gopay/payments-php-sdk)
[![Dependency Status](https://www.versioneye.com/user/projects/55ff8ef0601dd900150001e5/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55ff8ef0601dd900150001e5)

*****

## Installation

```bash
composer require gopay/payments-sdk-php --update-no-dev
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Private repository

Add this line to `composer.json`

```
{
    "repositories": [
        { "type": "vcs", "url": "git@bitbucket.org:edgedesigncz/gop-016-sdk-php.git" }
    ]
}
```

Then run `composer install --no-dev`

*****

## Basic usage

```
$gopay = GoPay\payments([
    'goid' => 'A',
    'clientId' => 'B',
    'clientSecret' => 'C',
    'isProductionMode' => false
]);
```

API method |
----------------------------------------------------------------------- |
[`$gopay->createPayment(array $payment)`](https://doc.gopay.com/en/#standard-payment) |
[`$gopay->getStatus($id)`](https://doc.gopay.com/en/#status-of-the-payment) |
[`$gopay->refund($id, $amount)`](https://doc.gopay.com/en/#refund-of-the-payment-(cancelation)) |
[`$gopay->createRecurrencePayment(array $payment)`](https://doc.gopay.com/en/#recurring-payment) |
[`$gopay->recurrenceOnDemand($id, array $payment)`](https://doc.gopay.com/en/#recurring-payment-on-demand) |
[`$gopay->recurrenceVoid($id)`](https://doc.gopay.com/en/#cancellation-of-the-recurring-payment) |
[`$gopay->createPreauthorizedPayment(array $payment)`](https://doc.gopay.com/en/#pre-authorized-payment) |
[`$gopay->preauthorizedCapture($id)`](https://doc.gopay.com/en/#charge-of-pre-authorized-payment) |
[`$gopay->preauthorizedVoid($id)`](https://doc.gopay.com/en/#cancellation-of-the-pre-authorized-payment) |

*****

## Cache access token

Access token expires after 30 minutes so it's expensive to use new token for every request.
Unfortunately it's default behavior of [`GoPay\Token\InMemoryTokenCache`](src/Token/InMemoryTokenCache.php).
But you can implement your cache and store tokens in Memcache, Redis, files, ... It's up to you.

Your cache must implement [`GoPay\Token\TokenCache`](src/Token/TokenCache.php) interface.
Be aware that there are two [scopes](https://doc.gopay.com/en/?shell#scope) (`TokenScope`).
So token must be cached for each scope. 
Below you can see example implementation of caching tokens in file (@todo test it :):


```
$gopay = GoPay\payments(['..config...'], new PrimitiveFileCache());
```

```
<?php

use GoPay\Token\TokenCache;
use GoPay\Token\AccessToken;

class PrimitiveFileCache implements TokenCache
{
    private $file;

    public function setScope($scope)
    {
        $this->file = __DIR__ . "/{$scope}";
    }

    public function isExpired()
    {
        return $this->loadTokenFromFile()->isExpired();
    }

    public function getAccessToken()
    {
        return $this->loadTokenFromFile()->getAccessToken();
    }

    public function setAccessToken($token, \DateTime $expirationDate)
    {
        $token = new AccessToken;
        $token->setToken($token, $expirationDate);
        file_put_contents($this->file, serialize($token);
    }

    private function loadToken()
    {
        if (file_exists($this->file)) {
            return unserialize(file_get_contents($this->file));
        }
        return new AccessToken;
    }
}

```