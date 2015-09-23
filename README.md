
# Gopay's PHP SDK for Payments REST API

[![License](https://poser.pugx.org/gopay/payments-php-sdk/license)](https://packagist.org/packages/gopay/payments-php-sdk)
[![Latest Stable Version](https://poser.pugx.org/gopay/payments-php-sdk/v/stable)](https://packagist.org/packages/gopay/payments-php-sdk)
[![Dependency Status](https://www.versioneye.com/user/projects/55ff8ef0601dd900150001e5/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55ff8ef0601dd900150001e5)

## Requirements

- PHP >= 5.4.0
- enabled extension `curl`, `json`

## Installation

```bash
composer require gopay/payments-sdk-php --update-no-dev
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Private repository

Add this line to `composer.json`

```json
{
    "repositories": [
        { "type": "vcs", "url": "git@bitbucket.org:edgedesigncz/gop-016-sdk-php.git" }
    ]
}
```

## Basic usage

```php
$gopay = GoPay\payments([
    'goid' => 'my goid',
    'clientId' => 'my id',
    'clientSecret' => 'my secret',
    'isProductionMode' => false,
    'scope' => GoPay\Definition\TokenScope::ALL,
    'language' => GoPay\Definition\Language::CZECH
]);
```

### Configuration

Required field | Data type | Documentation |
-------------- | --------- | ----------- |
`goid` | string | default GoPay account used in `createPayment` if `target` is not specified
`clientId` | string | https://doc.gopay.com/en/?shell#oauth |
`clientSecret` | string | https://doc.gopay.com/en/?shell#oauth |
`isProductionMode` | boolean | [test or production environment?](https://help.gopay.com/en/s/ey) |
`scope` | [`GoPay\Definition\TokenScope`](src/Definition/TokenScope.php) | https://doc.gopay.com/en/?shell#scope |
`language` | [`GoPay\Definition\Language`](src/Definition/Language.php) | default language used in `createPayment` if `lang` is not specified + used for [localization of errors](https://doc.gopay.com/en/?shell#return-errors)

### Available methods

API | SDK method |
--- | ---------- |
[Create standard payment](https://doc.gopay.com/en/#standard-payment) | `$gopay->createPayment(array $payment)` |
[Status of the payment](https://doc.gopay.com/en/#status-of-the-payment) | `$gopay->getStatus($id)` |
[Refund of the payment](https://doc.gopay.com/en/#refund-of-the-payment-(cancelation)) | `$gopay->refund($id, $amount)` |
[Create recurring payment](https://doc.gopay.com/en/#recurring-payment) | `$gopay->createPayment(array $payment)` |
[Recurring payment on demand](https://doc.gopay.com/en/#recurring-payment-on-demand) | `$gopay->recurrenceOnDemand($id, array $payment)` |
[Cancellation of the recurring payment](https://doc.gopay.com/en/#cancellation-of-the-recurring-payment) | `$gopay->recurrenceVoid($id)` |
[Create pre-authorized payment](https://doc.gopay.com/en/#pre-authorized-payment) | `$gopay->createPayment(array $payment)` |
[Charge of pre-authorized payment](https://doc.gopay.com/en/#charge-of-pre-authorized-payment) | `$gopay->preauthorizedCapture($id)` |
[Cancellation of the pre-authorized payment](https://doc.gopay.com/en/#cancellation-of-the-pre-authorized-payment) | `$gopay->preauthorizedVoid($id)` |

### SDK response? Has my call succeed?

SDK returns wrapped API response. Every method returns
[`GoPay\Http\Response` object](src/Http/Response.php). Structure of `json/__toString`
should be same as in [documentation](https://doc.gopay.com/en).
SDK throws no exception. Please create an issue if you catch one. 

```php
$response = $gopay->createPayment([/* define your payment  */]);
if ($response->hasSucceed()) {
    echo "hooray, API returned {$response}";
    return $response->json['gw_url']; // url for initiation of gateway
} else {
    // errors format: https://doc.gopay.com/en/?shell#http-result-codes
    echo "oops, API returned {$response->statusCode}: {$response}";
}

```

Method | Description |
------ | ---------- |
`$response->hasSucceed()` | checks if API returns status code _200_ |
`$response->json` | decoded response, returned objects are converted into associative arrays |
`$response->statusCode` | HTTP status code |
`$response->__toString()` | raw body from HTTP response |

### Are required fields and allowed values validated?

**No.** API [validates fields](https://doc.gopay.com/en/?shell#return-errors) pretty extensively
so there is no need to duplicate validation in SDK. It would only introduce new type of error.
Or we would have to perfectly simulate API error messages. That's why SDK just calls API which
behavior is well documented in [doc.gopay.com](https://doc.gopay.com/en).

*****

## Advanced usage

### Enums ([Code lists](https://doc.gopay.com/en/?php#code-lists))

Instead of hardcoding bank codes string you can use predefined enums. 
Check using enums in  [create-payment example](/examples/create-payment.php)

Type | Description |
---- | ----------- |
[Language](/src/Definition/Language.php) | Payment language, localization of error messages |
[Token scope](/src/Definition/TokenScope.php) | Authorization scope for [OAuth2](https://doc.gopay.com/en/?php#oauth) |
[Payment enums](/src/Definition/Payment) | Enums for creating payment |
[Response enums](/src/Definition/Response) | Result of creating payment, executing payment operations |

### Framework integration

* [Symfony2](/examples/symfony.md)

### Cache access token

Access token expires after 30 minutes so it's expensive to use new token for every request.
Unfortunately it's default behavior of [`GoPay\Token\InMemoryTokenCache`](src/Token/InMemoryTokenCache.php).
But you can implement your cache and store tokens in Memcache, Redis, files, ... It's up to you.

Your cache must implement template methods from [`GoPay\Token\TokenCache`](src/Token/TokenCache.php).
Be aware that there are two [scopes](https://doc.gopay.com/en/?shell#scope) (`TokenScope`).
So token must be cached for each scope. 
Below you can see example implementation of caching tokens in file (@todo test it :):


```php
// register cache in optional service configuration
$gopay = GoPay\payments(
    [/* your config */],
    ['cache' => new PrimitiveFileCache()]
);
```

```php
<?php

use GoPay\Token\TokenCache;
use GoPay\Token\AccessToken;

class PrimitiveFileCache extends TokenCache
{
    private $file;

    public function setScope($scope)
    {
        $this->file = __DIR__ . "/{$scope}";
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
        return new AccessToken; 
    }
}

```

Method `getAccessToken` can return `null`, this method is called only if `isExpired => false`.
But if you are testing your cache then return `new AccessToken`, so you don't get
null pointer errors like _Trying to get property of non-object_.


### Log communication with API

You can log every request and response from communication with API. Check available loggers
below. Or you can implement your own logger,
just implement [`GoPay\Http\Log\Logger`](src/Http/Log/Logger.php)) interface.

```php
// register logger in optional service configuration
$gopay = GoPay\payments(
    [/* your config */],
    ['logger' => new GoPay\Http\Log\PrintHttpRequest()]
);
```

Available logger | Description |
---------------- | ----------- |
[NullLogger](/src/Http/Log/NullLogger.php) | Default logger which does nothing |
[PrintHttpRequest](/src/Http/Log/PrintHttpRequest.php) | Prints basic information about request and response, used in [remote tests](tests/remote/GivenGoPay.php) |