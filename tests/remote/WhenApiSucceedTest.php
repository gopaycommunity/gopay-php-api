<?php

namespace GoPay;

class WhenApiSucceedTest extends GivenGoPay
{
    public function testCreatePaymentAndGetItsStatus()
    {
        $this->givenCustomer();
        $this->whenCustomerCalls('createPayment', [
            'amount' => 1,
            'currency' => Definition\Payment\Currency::CZECH_CROWNS,
            'order_number' => 'order-test - ' . date('Y-m-d H:i:s'),
            'order_description' => 'php test',
            'callback' => [
                'return_url' => 'http://www.your-url.tld/return',
                'notification_url' => 'http://www.your-url.tld/notify'
            ]
        ]);
        $this->apiShouldReturn('gw_url', containsString('.gopay.'));
        
        $idOfCreatedPayment = $this->response->json['id'];
        $this->whenCustomerCalls('getStatus', $idOfCreatedPayment);
        $this->apiShouldReturn('state', is(Definition\Response\PaymentStatus::CREATED));
    }
}
