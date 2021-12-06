<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Definition\Payment\BankSwiftCode;
use GoPay\Definition\Payment\Recurrence;

$gopay = GoPay\payments([
    'goid' => 'my goid',
    'clientId' => 'my id',
    'clientSecret' => 'my secret',
    'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
    'language' => Language::CZECH
]);

// recurrent payment must have field ''
$recurrentPayment = [
    'recurrence' => [
        'recurrence_cycle' => Recurrence::DAILY,
        'recurrence_period' => "7",
        'recurrence_date_to' => '2015-12-31'
    ]
];

// pre-authorized payment must have field 'preauthorization'
$preauthorizedPayment = [
    'preauthorization' => true
];

$response = $gopay->createPayment([
    'payer' => [
        'default_payment_instrument' => PaymentInstrument::BANK_ACCOUNT,
        'allowed_payment_instruments' => [PaymentInstrument::BANK_ACCOUNT],
        'default_swift' => BankSwiftCode::FIO_BANKA,
        'allowed_swifts' => [BankSwiftCode::FIO_BANKA, BankSwiftCode::MBANK],
        'contact' => [
            'first_name' => 'Zbynek',
            'last_name' => 'Zak',
            'email' => 'zbynek.zak@gopay.cz',
            'phone_number' => '+420777456123',
            'city' => 'C.Budejovice',
            'street' => 'Plana 67',
            'postal_code' => '373 01',
            'country_code' => 'CZE',
        ],
    ],
    'amount' => 150,
    'currency' => Currency::CZECH_CROWNS,
    'order_number' => '001',
    'order_description' => 'pojisteni01',
    'items' => [
        ['name' => 'item01', 'amount' => 50],
        ['name' => 'item02', 'amount' => 100],
    ],
    'additional_params' => [
        array('name' => 'invoicenumber', 'value' => '2015001003')
    ],
    'callback' => [
        'return_url' => 'http://www.your-url.tld/return',
        'notification_url' => 'http://www.your-url.tld/notify'
    ],
    'lang' => Language::CZECH, // if lang is not specified, then default lang is used
]);

if ($response->hasSucceed()) {
    // response format: https://doc.gopay.com/en/?shell#standard-payment
    echo "hooray, API returned {$response}<br />\n";
    echo "Gateway url: {$response->json['gw_url']}";
} else {
    // errors format: https://doc.gopay.com/en/?shell#http-result-codes
    echo "oops, API returned {$response->statusCode}: {$response}";
}
