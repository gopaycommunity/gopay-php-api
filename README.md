
# GoPay's PHP SDK for Payments REST API

[![License](https://poser.pugx.org/gopay/payments-sdk-php/license)](https://packagist.org/packages/gopay/payments-sdk-php)
[![Latest Stable Version](https://poser.pugx.org/gopay/payments-sdk-php/v/stable)](https://packagist.org/packages/gopay/payments-sdk-php)
[![Total Downloads](https://poser.pugx.org/gopay/payments-sdk-php/downloads)](https://packagist.org/packages/gopay/payments-sdk-php)
[![Monthly Downloads](https://poser.pugx.org/gopay/payments-sdk-php/d/monthly)](https://packagist.org/packages/gopay/payments-sdk-php)
[![Dependency Status](https://www.versioneye.com/user/projects/570b383e2aca6b000e0dea95/badge.svg)](https://www.versioneye.com/user/projects/570b383e2aca6b000e0dea95)

## Requirements

- PHP >= 8.1
- enabled extension `curl`, `json`

## Installation

The simplest way to install SDK is to use [Composer](https://getcomposer.org/doc/00-intro.md):

```bash
composer require gopay/payments-sdk-php
```

## Basic usage

```php
// minimal configuration
$gopay = GoPay\Api::payments([
    'goid' => 'my goid',
    'clientId' => 'my id',
    'clientSecret' => 'my secret',
    'gatewayUrl' => 'gateway url'
]);

// full configuration
$gopay = GoPay\Api::payments([
    'goid' => 'my goid',
    'clientId' => 'my id',
    'clientSecret' => 'my secret',
    'gatewayUrl' => 'gateway url',
    'scope' => GoPay\Definition\TokenScope::ALL,
    'language' => GoPay\Definition\Language::CZECH,
    'timeout' => 30
]);
```

### Configuration

#### Required fields

Required field | Data type | Documentation |
-------------- | --------- | ----------- |
`goid` | string | default GoPay account used in `createPayment` if `target` is not specified
`clientId` | string | <https://doc.gopay.com/#access-token> |
`clientSecret` | string | <https://doc.gopay.com/#access-token> |
`gatewayUrl` | string | [test or production environment?](https://help.gopay.com/en/s/uY) |

#### Optional fields

Optional field | Data type | Default value | Documentation |
-------------- | --------- | ------------- | ------------- |
`scope` | string | [`GoPay\Definition\TokenScope::ALL`](src/Definition/TokenScope.php) | <https://doc.gopay.com/#access-token> |
`language` | string | [`GoPay\Definition\Language::ENGLISH`](src/Definition/Language.php) | language used in `createPayment` if `lang` is not specified + used for [localization of errors](https://doc.gopay.com/#errors)
`timeout` | int | 30 | Browser timeout in seconds |

### Available methods

API | SDK method |
--- | ---------- |
[Create a payment](https://doc.gopay.com#payment-creation) | `$gopay->createPayment(array $payment)` |
[Get status of a payment](https://doc.gopay.com#payment-inquiry) | `$gopay->getStatus($id)` |
[Refund a payment](https://doc.gopay.com#payment-refund) | `$gopay->refundPayment($id, $amount)` |
[Create a recurring payment](https://doc.gopay.com#creating-a-recurrence) | `$gopay->createRecurrence($id, array $payment)` |
[Cancel a recurring payment](https://doc.gopay.com#void-a-recurring-payment) | `$gopay->voidRecurrence($id)` |
[Capture a preauthorized payment](https://doc.gopay.com#capturing-a-preauthorized-payment) | `$gopay->captureAuthorization($id)` |
[Capture a preauthorized payment partially](https://doc.gopay.com#partially-capturing-a-preauthorized-payment) | `$gopay->captureAuthorizationPartial($id, array $capturePayment)` |
[Void a preauthorized payment](https://doc.gopay.com#voiding-a-preauthorized-payment) | `$gopay->voidAuthorization($id)` |
[Get payment card details](https://doc.gopay.com#payment-card-inquiry) | `$gopay->getCardDetails($cardId)` |
[Delete a saved card](https://doc.gopay.com#payment-card-deletion) | `$gopay->deleteCard($cardId)` |
[Get allowed payment methods for a currency](https://doc.gopay.com#available-payment-methods-for-a-currency) | `$gopay->getPaymentInstruments($goid, $currency)` |
[Get all allowed payment methods](https://doc.gopay.com#all-available-payment-methods) | `$gopay->getPaymentInstrumentsAll($goid)` |
[Generate an account statement](https://doc.gopay.com#account-statement) | `$gopay->getAccountStatement(array $accountStatement)`

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
`$response->rawBody` | raw body from HTTP response |

### Are required fields and allowed values validated?

**No.** API [validates fields](https://doc.gopay.com/#error) pretty extensively
so there is no need to duplicate validation in SDK. It would only introduce new type of error.
Or we would have to perfectly simulate API error messages. That's why SDK just calls API which
behavior is well documented in [doc.gopay.com](https://doc.gopay.com).

*****

## Advanced usage

### Initiation of the payment gateway

```php
// create payment and pass url to template 
$response = $gopay->createPayment([/* define your payment  */]);
if ($response->hasSucceed()) {

        $gatewayUrl => $response->json['gw_url'],
        $embedJs => $gopay->urlToEmbedJs()
        // render template
}
```

#### [Inline gateway](https://doc.gopay.com/#inline)

```php
<form action="<?= $gatewayUrl ?>" method="post" id="gopay-payment-button">
  <button name="pay" type="submit">Pay</button>
  <script type="text/javascript" src="<?= $embedJs ?>"></script>
</form>
```

#### [Redirect gateway](https://doc.gopay.com/#redirect)

```php
<form action="<?= $gatewayUrl ?>" method="post">
  <button name="pay" type="submit">Pay</button>
</form>
```

#### [Asynchronous initialization using JavaScript](/examples/js-initialization.md)

### Enums ([Code lists](https://doc.gopay.com/#ciselniky))

Instead of hardcoding bank codes string you can use predefined enums.
Check using enums in  [create-payment example](/examples/create-payment.php)

Type | Description |
---- | ----------- |
[Language](/src/Definition/Language.php) | Payment language, localization of error messages |
[Token scope](/src/Definition/TokenScope.php) | Authorization scope for [OAuth2](https://doc.gopay.com/#access-token) |
[Payment enums](/src/Definition/Payment) | Enums for creating payment |
[Response enums](/src/Definition/Response) | Result of creating payment, executing payment operations |
[ItemType enums](/src/Definition/Payment/PaymentItemType.php) | Type of an item |
[VatRate enums](/src/Definition/Payment/VatRate.php) | VatRate of an item |

### Framework integration

- [Symfony2](/examples/symfony.md)

### Cache access token

Access token expires after 30 minutes so it's expensive to use new token for every request.
Unfortunately it's default behavior of [`GoPay\Token\InMemoryTokenCache`](src/Token/InMemoryTokenCache.php).
But you can implement your cache and store tokens in Memcache, Redis, files, ... It's up to you.

Your cache must implement [`GoPay\Token\TokenCache` interface](src/Token/TokenCache.php).
Be aware that there are two [scopes](https://doc.gopay.com/#scope) (`TokenScope`) and
SDK can be used for different clients (`clientId`, `gatewayUrl`). So `client` passed to
methods is unique identifier (`string`) that is built for current environment.
Below you can see example implementation of caching tokens in file:

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

class PrimitiveFileCache implements TokenCache
{
    public function setAccessToken($client, AccessToken $t)
    {
        file_put_contents(__DIR__ . "/{$client}", serialize($t));
    }

    public function getAccessToken($client)
    {
        $file = __DIR__ . "/{$client}";
        if (file_exists($file)) {
            return unserialize(file_get_contents($file));
        }
        return null; 
    }
}

```

### Log HTTP communication

You can log every request and response from communication with API. Check available loggers
below. Or you can implement your own logger,
just implement [`GoPay\Http\Log\Logger`](src/Http/Log/Logger.php) interface.

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
[PrintHttpRequest](/src/Http/Log/PrintHttpRequest.php) | Prints basic information about request and response, used in [remote tests](tests/integration/GivenGoPay.php#GivenGoPay.php-26) |

## Contributing

Contributions from others would be very much appreciated! Send
[pull request](https://github.com/gopaycommunity/gopay-php-api/pulls)/
[issue](https://github.com/gopaycommunity/gopay-php-api/issues). Thanks!

## License

Copyright (c) 2015 GoPay.com. MIT Licensed,
see [LICENSE](/LICENSE) for details.
