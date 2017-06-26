<?php

namespace GoPay;

class PaymentsSupercash extends Payments
{

    const SUB_TYPE_PREPAID = 'PREPAID';
    const SUB_TYPE_POSTPAID = 'POSTPAID';

    /**
     * Vytvoří jeden Supercash kupón
     * @param array $supercashCoupon
     * @return Http\Response
     */
    public function createSupercashCoupon(array $supercashCoupon)
    {
        $coupon = ['go_id' => $this->gopay->getConfig('goid')] + $supercashCoupon;

        return $this->api('supercash/coupon', GoPay::JSON, $coupon);
    }


    /**
     * Vytvoření superCASH batche - zažádá o vytvoření dávky kupónů supercash
     * @param array $supercashCouponBatch
     * @return Http\Response
     */
    public function createSupercashCouponBatch(array $supercashCouponBatch)
    {
        $batch = ["go_id" => $this->gopay->getConfig('goid')] + $supercashCouponBatch;

        return $this->api("supercash/coupon/batch", GoPay::JSON, $batch);
    }


    /**
     * Stav vytvoření batche - vrátí stav dávky kupónů
     *
     * @param string $batchId
     * @return Http\Response
     */
    public function getSupercashCouponBatchStatus($batchId)
    {
        return $this->api("batch/" . $batchId, GoPay::FORM);
    }


    /**
     * Detaily superCASH kupónů batche - vrátí dávku kupónů pokud je již zpracovaná
     *
     * @param string $batchId
     * @return Http\Response
     */
    public function getSupercashCouponBatch($batchId)
    {
        return $this->api("supercash/coupon/find?batch_request_id=" . $batchId
                . "&go_id={$this->gopay->getConfig('goid')}", GoPay::FORM);
    }


    /**
     * Detaily superCASH kupónů platby - Nalezne kupóny Supercash dle $paymentSessionId (vstup může být pole)
     *
     * @param int|array $paymentSessionId
     * @return Http\Response
     */
    public function findSupercashCoupons($paymentSessionId)
    {
        $queryData = is_array($paymentSessionId) ? array_values($paymentSessionId) : [$paymentSessionId];

        return $this->api('supercash/coupon/find?payment_session_id_list=' . implode(",", $queryData)
                . "&go_id={$this->gopay->getConfig('goid')}", GoPay::FORM);
    }


    /**
     * Detaily superCASH kupónu - vrátí stav jednoho kupónu Supercash
     *
     * @param string $couponId
     * @return Http\Response
     */
    public function getSupercashCoupon($couponId)
    {
        return $this->api("supercash/coupon/{$couponId}", GoPay::FORM);
    }


}
