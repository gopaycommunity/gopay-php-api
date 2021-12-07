<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GoPay\Definition\TokenScope;
use GoPay\Definition\Language;

$gopay = GoPay\payments([
    'goid' => 'my goid',
    'clientId' => 'my id',
    'clientSecret' => 'my secret',
    'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
    'scope' => TokenScope::ALL,
    'language' => Language::CZECH
]);
$response = $gopay->getStatus('payment id');

if ($response->hasSucceed()) {
    // response format: https://doc.gopay.com/en/?shell#status-of-the-payment
    echo "hooray, API returned {$response}<br />\n";
} else {
    // errors format: https://doc.gopay.com/en/?shell#http-result-codes
    echo "oops, API returned {$response->statusCode}: {$response}";
}
