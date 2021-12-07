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
        'clientId' => 'my clientId',
        'clientSecret' => 'my clientSecret',
        'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
        'language' => Language::CZECH
]);

// Create standard payment with eet
$response = $gopay->createPayment([
        'payer' => [
                'default_payment_instrument' => PaymentInstrument::BANK_ACCOUNT,
                'allowed_payment_instruments' => [PaymentInstrument::BANK_ACCOUNT],
                'default_swift' => BankSwiftCode::FIO_BANKA,
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
                ]
        ],
        'amount' => 139951,
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
                        'amount' => 19961,
                        'count' => 1,
                        'vat_rate' => VatRate::RATE_3
                ]
        ],
        'eet' => [
                'celk_trzba' => 139951,
                'zakl_dan1' => 99160,
                'dan1' => 20830,
                'zakl_dan2' => 17358,
                'dan2' => 2603,
                'mena' => Currency::CZECH_CROWNS
        ],
        'additional_params' => [[
                'name' => 'invoicenumber',
                'value' => '2015001003'
        ]],
        'callback' => [
                'return_url' => 'https://www.eshop.cz/return',
                'notification_url' => 'https://www.eshop.cz/notify'
        ],
        'lang' => Language::CZECH
]);

if ($response->hasSucceed()) {
        // response format: https://doc.gopay.com/en/?shell#standard-payment
        echo "hooray, API returned {$response}<br />\n";
        echo "Gateway url: {$response->json['gw_url']}";
} else {
        // errors format: https://doc.gopay.com/en/?shell#http-result-codes
        echo "oops, API returned {$response->statusCode}: {$response}";
}
