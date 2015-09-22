<?php

require_once __DIR__ . '/../vendor/autoload.php';

$gopay = GoPay\payments([
    'goid' => 'A',
    'clientId' => 'B',
    'clientSecret' => 'C',
    'isProductionMode' => false
]);
$response = $gopay->getStatus('payment id');

if ($response->hasSucceed()) {
    // response format: https://doc.gopay.com/en/?shell#status-of-the-payment
    echo "hooray, API returned {$response}<br />\n";
} else {
    // errors format: https://doc.gopay.com/en/?shell#http-result-codes
    echo "oops, API returned {$response->statusCode}: {$response}";
}
