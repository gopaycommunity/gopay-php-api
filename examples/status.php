<?php

require_once __DIR__ . '/../vendor/autoload.php';

$gopay = GoPay\payments([
    'goid' => 'A',
    'clientId' => 'B',
    'clientSecret' => 'C',
    'isProductionMode' => false
]);
$gopay->getStatus('payment id');
