<?php

namespace GoPay;

require_once 'TestUtils.php';

use GoPay\Definition\Language;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Definition\Payment\BankSwiftCode;
use GoPay\Definition\Payment\Recurrence;
use GoPay\Definition\Payment\VatRate;
use GoPay\Definition\Payment\PaymentItemType;

/**
 * Class EETTests
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class EETTests extends \PHPUnit_Framework_TestCase
{

    private $gopay;

    protected function setUp()
    {
        $this->gopay = TestUtils::setupEET();
    }

    private function createBaseEETPayment()
    {
        $baseEETPayment = [
                'payer' => [
                        'allowed_payment_instruments' => [PaymentInstrument::BANK_ACCOUNT,
                                PaymentInstrument::PAYMENT_CARD],
                        'allowed_swifts' => [BankSwiftCode::RAIFFEISENBANK, BankSwiftCode::CESKA_SPORITELNA],
                    //'default_swift' => BankSwiftCode::FIO_BANKA,
                    //'default_payment_instrument' => PaymentInstrument::BANK_ACCOUNT,
                        'contact' => [
                                'email' => 'test.test@gopay.cz',
                        ],
                ],
                'order_number' => 'EET9876',
                'amount' => 139950,
                'currency' => Currency::CZECH_CROWNS,
                'order_description' => 'EET9876Description',
                'lang' => Language::CZECH,
                'additional_params' => [
                        array('name' => 'invoicenumber', 'value' => '2015001003')
                ],
                'items' => [
                        ['name' => 'Pocitac Item1', 'amount' => '119990', 'count' => '1', 'vat_rate' => VatRate::RATE_4,
                                'type' => PaymentItemType::ITEM, 'ean' => '1234567890123',
                                'product_url' => 'https://www.eshop123.cz/pocitac'],
                        ['name' => 'Oprava Item2', 'amount' => '19960', 'count' => '1', 'vat_rate' => VatRate::RATE_3,
                                'type' => PaymentItemType::ITEM, 'ean' => '1234567890189',
                                'product_url' => 'https://www.eshop123.cz/pocitac/oprava'],
                ],
                'callback' => [
                        'return_url' => 'https://eshop123.cz/return',
                        'notification_url' => 'https://eshop123.cz/notify'
                ],
                'eet' => [
                        'celk_trzba' => 139950,
                        'zakl_dan1' => 99165,
                        'dan1' => 20825,
                        'zakl_dan2' => 17357,
                        'dan2' => 2603,
                        'mena' => Currency::CZECH_CROWNS
                ]
        ];

        return $baseEETPayment;
    }

    private function createEETPaymentObject($baseEETPayment)
    {
        $payment = $this->gopay->createPayment($baseEETPayment);

        echo print_r($payment->json, true);
        $st = json_encode($payment->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $payment->json['id'] . "\n");
            print_r("Payment gwUrl: " . $payment->json['gw_url'] . "\n");
            print_r("Payment state: " . $payment->json['state'] . "\n");
        }

        return $payment;
    }

    public function tCreateEETPayment()
    {
        $baseEETPayment = $this->createBaseEETPayment();
        $payment = $this->createEETPaymentObject($baseEETPayment);

    }

    public function tCreateRecurrentEETPayment()
    {
        $baseEETPayment = $this->createBaseEETPayment();

        $baseEETPayment['recurrence'] = [
                'recurrence_cycle' => Recurrence::WEEKLY,
                'recurrence_period' => "1",
                'recurrence_date_to' => '2018-04-01'
        ];

//        $baseEETPayment['recurrence'] = [
//                'recurrence_cycle' => Recurrence::ON_DEMAND,
//                'recurrence_date_to' => '2018-04-01'
//        ];

        $payment = $this->createEETPaymentObject($baseEETPayment);

        $st = json_encode($payment->json);
        if (strpos($st, 'error_code') === false) {
            print_r("Recurrence: ");
            echo print_r($payment->json['recurrence'], true);
        }
    }

    public function tNextOnDemandEET()
    {
        $nextEETPayment = [
                'amount' => 2000,
                'currency' => Currency::CZECH_CROWNS,
                'order_number' => 'EETOnDemand9876',
                'order_description' => 'EETOnDemand9876Description',
                'items' => [
                        ['name' => 'OnDemand Prodlouzena zaruka', 'amount' => '2000', 'count' => '1',
                                'vat_rate' => VatRate::RATE_4, 'type' => PaymentItemType::ITEM,
                                'ean' => '1234567890123',
                                'product_url' => 'https://www.eshop123.cz/pocitac/prodlouzena_zaruka'],
                ],
                'eet' => [
                        'celk_trzba' => 2000,
                        'zakl_dan1' => 1580,
                        'dan1' => 420,
                        'mena' => Currency::CZECH_CROWNS
                ]
        ];

        $EETOnDemandPayment = $this->gopay->createRecurrence(3049604610, $nextEETPayment);

        echo print_r($EETOnDemandPayment->json, true);
        $st = json_encode($EETOnDemandPayment->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $EETOnDemandPayment->json['id'] . "\n");
            print_r("Payment gwUrl: " . $EETOnDemandPayment->json['gw_url'] . "\n");
            print_r("Payment state: " . $EETOnDemandPayment->json['state'] . "\n");
        }

    }

    public function testEETPaymentStatus()
    {
        $EETPaymentId = 3049604714;
        $response = $this->gopay->getStatus($EETPaymentId);

        echo print_r($response->json, true);
        $st = json_encode($response->json);

        if (strpos($st, 'error_code') === false) {
            print_r("Payment ID: " . $response->json['id'] . "\n");
            print_r("Payment gwUrl: " . $response->json['gw_url'] . "\n");
            print_r("Payment state: " . $response->json['state'] . "\n");
        }
    }

    public function tEETPaymentRefund()
    {
        $refundObject = [
                'amount' => 139950,
                'items' => [
                        ['name' => 'Pocitac Item1', 'amount' => '119990', 'count' => '1', 'vat_rate' => VatRate::RATE_4,
                                'type' => PaymentItemType::ITEM, 'ean' => '1234567890123',
                                'product_url' => 'https://www.eshop123.cz/pocitac'],
                        ['name' => 'Oprava Item2', 'amount' => '19960', 'count' => '1', 'vat_rate' => VatRate::RATE_3,
                                'type' => PaymentItemType::ITEM, 'ean' => '1234567890189',
                                'product_url' => 'https://www.eshop123.cz/pocitac/oprava'],
                ],
                'eet' => [
                        'celk_trzba' => 139950,
                        'zakl_dan1' => 99165,
                        'dan1' => 20825,
                        'zakl_dan2' => 17357,
                        'dan2' => 2603,
                        'mena' => Currency::CZECH_CROWNS
                ]
        ];

        $response = $this->gopay->refundPayment(3049604714, $refundObject);

        echo print_r($response->json, true);
    }

    public function tEETReceiptFindByFilter()
    {
        $receiptFilter = [
                'date_from' => '2017-03-02',
                'date_to' => '2017-04-02',
                'id_provozovny' => 11
        ];

        $receipts = $this->gopay->findEETReceiptsByFilter($receiptFilter);

        echo print_r($receipts->json, true);
    }

    public function tEETReceiptFindByPaymentId()
    {
        $receipt = $this->gopay->getEETReceiptByPaymentId(3048429735);

        echo print_r($receipt->json, true);
    }

}
