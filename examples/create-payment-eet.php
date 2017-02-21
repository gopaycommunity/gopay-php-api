<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Definition\Payment\BankSwiftCode;
use GoPay\Definition\Payment\VatRate;
use GoPay\Definition\Payment\PaymentItemType;

$gopay = GoPay\payments([
    'goid' => 'my goid',
    'clientId' => 'client_id@example.com',
    'clientSecret' => 'my secret',
    'isProductionMode' => false,
    'language' => Language::CZECH
]);

// Create standard payment
$payment = [
    'payer' => [
        'default_payment_instrument' => PaymentInstrument::BANK_ACCOUNT,
        'allowed_payment_instruments' => [PaymentInstrument::BANK_ACCOUNT],
        'default_swift' => BankSwiftCode::FIO_BANKA, 'FIOBCZPP',
        'allowed_swifts' => [BankSwiftCode::FIO_BANKA, BankSwiftCode::MBANK],
        'contact' => [
            'first_name' => 'Zbynek',
            'last_name' => 'Zak',
            'email' => 'test@test.cz',
            'phone_number' => '+420777456123',
            'city' => 'C.Budejovice',
            'street' => 'Plana 67',
            'postal_code' => '373 01',
            'country_code' => 'CZE'
        ],
    ],
    'target' => ['type' => 'ACCOUNT', 'goid' => '8123456789'],
    'amount' => 1000,
    'currency' => Currency::CZECH_CROWNS,
    'order_number' => '001',
    'order_description' => 'obuv',
    'items' => [
        [
            'type' => 'ITEM',
            'name' => 'obuv',
            'product_url' => 'https://www.eshop.cz/boty/lodicky',
            'ean' => 1234567890123,
            'amount' => 119990,
            'count' => 1,
            'vat_rate' => VatRate::RATE_4
        ],
        [
            'type' => PaymentItemType::ITEM,
            'name' => 'oprava podpatku',
            'product_url' => 'https://www.eshop.cz/boty/opravy',
            'ean' => 1234567890189,
            'amount' => 19960,
            'count' => 1,
            'vat_rate' => VatRate::RATE_3
        ],
    ],
    'additional_params' => [
        ['name' => 'invoicenumber', 'value' => '2015001003'],
    ],
    'callback' => [
        'return_url' => 'https://www.eshop.cz/return',
        'notification_url' => 'https://www.eshop.cz/notify'
    ],
    'lang' => Language::CZECH
];

$eet = [
    'eet' => [
        'celk_trzba' => 139950,
        'zakl_dan1' => 99165,
        'dan1' => 20825,
        'zakl_dan2' => 17357,
        'dan2' => 2604,
        'mena' => Currency::CZECH_CROWNS
    ]
];

$paymentWithEet = $payment + $eet;
$createPaymentResponse = $gopay->createPayment($paymentWithEet);

// Refund payment
$refundPaymentResponse = $gopay->refundPayment(3000006620, [
    'amount' => 119990,
    'items' => [
        [
            'type' => PaymentItemType::ITEM,
            'name' => 'lodicky',
            'product_url' => 'https://www.eshop.cz/boty/damske/lodicky-cervene',
            'ean' => 1234567890123,
            'amount' => -119990,
            'count' => 1,
            'vat_rate' => VatRate::RATE_4
        ],
    ],
    'eet' => [
        'celk_trzba' => -119990,
        'zakl_dan1' => -99165,
        'dan1' => -20825,
        'dan2' => -2604
    ],
]);

// Create reccurent payment
$recurrence = [
    'recurrence' => [
        'recurrence_cycle' => 'DAY',
        'recurrence_period' => '7',
        'recurrence_date_to' => '2015-12-31'
    ]
];

$reccurentPaymentResponse = $gopay->createPayment($paymentWithEet + $recurrence);

// After reccurent payment is created, you can withold money
$reccurenceResponse = $gopay->createRecurrence(3000006620, [
    'amount' => '500',
    'currency' => Currency::CZECH_CROWNS,
    'order_number' => 'Nakup',
    'order_description' => '2016-0001254',
    'items' => [
        [
            'type' => PaymentItemType::ITEM,
            'name' => 'lodicky',
            'product_url' => 'https://www.eshop.cz/boty//lodicky',
            'ean' => 1234567890123,
            'amount' => 119990,
            'count' => 1,
            'vat_rate' => VatRate::RATE_4
        ],
    ],
    'eet' => [
        'celk_trzba' => 119990,
        'zakl_dan1' => 99165,
        'dan1' => 20825,
        'mena' => Currency::CZECH_CROWNS
    ],
    'additional_params' => [
        ['name' => 'invoicenumber', 'value' => '2016001004'],
    ]
]);
