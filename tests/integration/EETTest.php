<?php

namespace GoPay\Tests\Integration;

use GoPay\Definition\Language;
use GoPay\Definition\Payment\BankSwiftCode;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;
use GoPay\Definition\Payment\PaymentItemType;
use GoPay\Definition\Payment\Recurrence;
use GoPay\Definition\Response\PaymentStatus;
use GoPay\Definition\Response\RecurrenceState;
use GoPay\Payments;

/**
 * Class EETTests
 *
 * @package GoPay
 *
 * To execute test for certain method properly it is necessary to add prefix 'test' to its name.
 */
class EETTest extends IntegrationTestCase
{
    protected function createPayments(): Payments
    {
        return TestUtils::setupEET();
    }

    /**
     * @return array<string, mixed>
     */
    private function createBaseEETPayment(): array
    {
        $baseEETPayment = [
            'payer' => [
                'allowed_payment_instruments' => [
                    PaymentInstrument::BANK_ACCOUNT,
                    PaymentInstrument::PAYMENT_CARD,
                ],
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
                ['name' => 'invoicenumber', 'value' => '2015001003'],
            ],
            'items' => [
                [
                    'name' => 'Pocitac Item1', 'amount' => '119990', 'count' => '1', 'vat_rate' => 21,
                    'type' => PaymentItemType::ITEM, 'ean' => '1234567890123',
                    'product_url' => 'https://www.eshop123.cz/pocitac',
                ],
                [
                    'name' => 'Oprava Item2', 'amount' => '19960', 'count' => '1', 'vat_rate' => 15,
                    'type' => PaymentItemType::ITEM, 'ean' => '1234567890189',
                    'product_url' => 'https://www.eshop123.cz/pocitac/oprava',
                ],
            ],
            'callback' => [
                'return_url' => 'https://eshop123.cz/return',
                'notification_url' => 'https://eshop123.cz/notify',
            ],
            'eet' => [
                'celk_trzba' => 139950,
                'zakl_dan1' => 99165,
                'dan1' => 20825,
                'zakl_dan2' => 17357,
                'dan2' => 2603,
                'mena' => Currency::CZECH_CROWNS,
            ],
        ];

        return $baseEETPayment;
    }

    public function testCreateEETPayment(): void
    {
        $baseEETPayment = $this->createBaseEETPayment();

        $response = $this->gopay->createPayment($baseEETPayment);

        $this->assertPaymentCreated($response, $baseEETPayment);
    }

    public function testCreateRecurrentEETPayment(): void
    {
        $baseEETPayment = $this->createBaseEETPayment();

        $baseEETPayment['recurrence'] = [
            'recurrence_cycle' => Recurrence::WEEKLY,
            'recurrence_period' => '1',
            'recurrence_date_to' => '2100-04-01',
        ];

        //        $baseEETPayment['recurrence'] = [
        //                'recurrence_cycle' => Recurrence::ON_DEMAND,
        //                'recurrence_date_to' => '2018-04-01'
        //        ];

        $response = $this->gopay->createPayment($baseEETPayment);

        $payment = $this->assertPaymentCreated($response, $baseEETPayment);
        $this->assertSame(
            [
                'recurrence_cycle' => Recurrence::WEEKLY,
                'recurrence_period' => 1,
                'recurrence_date_to' => '2100-04-01',
                'recurrence_state' => RecurrenceState::REQUESTED,
            ],
            $payment['recurrence'] ?? null
        );
    }

    /** Returns an error, as the recurrence for the payment id '3049604610' has been already stopped. */
    public function testNextOnDemandEET(): void
    {
        $nextEETPayment = [
            'amount' => 2000,
            'currency' => Currency::CZECH_CROWNS,
            'order_number' => 'EETOnDemand9876',
            'order_description' => 'EETOnDemand9876Description',
            'items' => [
                [
                    'name' => 'OnDemand Prodlouzena zaruka', 'amount' => '2000', 'count' => '1',
                    'vat_rate' => 21, 'type' => PaymentItemType::ITEM,
                    'ean' => '1234567890123',
                    'product_url' => 'https://www.eshop123.cz/pocitac/prodlouzena_zaruka',
                ],
            ],
            'eet' => [
                'celk_trzba' => 2000,
                'zakl_dan1' => 1580,
                'dan1' => 420,
                'mena' => Currency::CZECH_CROWNS,
            ],
        ];

        $response = $this->gopay->createRecurrence(3049604610, $nextEETPayment);

        $this->assertApiError($response, 409, 'PAYMENT_RECURRENCE_STOPPED');

        TestUtils::debug($response->json);
    }

    public function testEETPaymentStatus(): void
    {
        $EETPaymentId = 3049604714;

        $response = $this->gopay->getStatus($EETPaymentId);

        $payment = $this->assertSucceeded($response);
        $this->assertSame($EETPaymentId, $payment['id'] ?? null);
        $this->assertContains($payment['state'] ?? null, (new \ReflectionClass(PaymentStatus::class))->getConstants());
        $this->assertIsArray($payment['target'] ?? null);
        $this->assertSame(TestUtils::GO_ID_EET, (string) ($payment['target']['goid'] ?? null));
        $this->assertIsArray($payment['eet_code'] ?? null);
        $this->assertIsString($payment['eet_code']['fik'] ?? null);

        TestUtils::debug($payment);
    }

    public function testEETPaymentRefund(): void
    {
        $this->markTestSkipped('Refunds the fixed sandbox payment 3049604714, the result depends on whether it has been refunded already.');

        // @phpstan-ignore deadCode.unreachable (test is skipped, body kept for re-enabling)
        $refundObject = [
            'amount' => 139950,
            'items' => [
                [
                    'name' => 'Pocitac Item1', 'amount' => '119990', 'count' => '1', 'vat_rate' => 21,
                    'type' => PaymentItemType::ITEM, 'ean' => '1234567890123',
                    'product_url' => 'https://www.eshop123.cz/pocitac',
                ],
                [
                    'name' => 'Oprava Item2', 'amount' => '19960', 'count' => '1', 'vat_rate' => 15,
                    'type' => PaymentItemType::ITEM, 'ean' => '1234567890189',
                    'product_url' => 'https://www.eshop123.cz/pocitac/oprava',
                ],
            ],
            'eet' => [
                'celk_trzba' => 139950,
                'zakl_dan1' => 99165,
                'dan1' => 20825,
                'zakl_dan2' => 17357,
                'dan2' => 2603,
                'mena' => Currency::CZECH_CROWNS,
            ],
        ];

        $response = $this->gopay->refundPayment(3049604714, $refundObject);

        $this->assertSucceeded($response);

        TestUtils::debug($response->json);
    }

    public function testEETReceiptFindByFilter(): void
    {
        $receiptFilter = [
            'date_from' => '2017-03-02',
            'date_to' => '2017-04-02',
            'id_provozovny' => 11,
        ];

        $response = $this->gopay->findEETReceiptsByFilter($receiptFilter);

        $receipts = $this->assertSucceeded($response);
        $this->assertNotEmpty($receipts);
        $this->assertTrue(array_is_list($receipts), 'Receipts must be a list');
        foreach ($receipts as $receipt) {
            $this->assertIsArray($receipt);
            $this->assertIsInt($receipt['payment_id'] ?? null);
            $this->assertSame(11, $receipt['id_provoz'] ?? null);
            $this->assertIsString($receipt['dat_trzby'] ?? null);
            $date = substr($receipt['dat_trzby'], 0, 10);
            $this->assertGreaterThanOrEqual('2017-03-02', $date);
            $this->assertLessThanOrEqual('2017-04-02', $date);
        }

        TestUtils::debug($receipts);
    }

    public function testEETReceiptFindByPaymentId(): void
    {
        $paymentId = 3048429735;

        $response = $this->gopay->getEETReceiptByPaymentId($paymentId);

        $receipts = $this->assertSucceeded($response);
        $this->assertNotEmpty($receipts);
        $this->assertTrue(array_is_list($receipts), 'Receipts must be a list');
        foreach ($receipts as $receipt) {
            $this->assertIsArray($receipt);
            $this->assertSame($paymentId, $receipt['payment_id'] ?? null);
            $this->assertIsString($receipt['fik'] ?? null);
        }

        TestUtils::debug($receipts);
    }
}
