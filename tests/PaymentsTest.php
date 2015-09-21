<?php

namespace GoPay;

class PaymentsTest extends \PHPUnit_Framework_TestCase
{
    private $id = 'irrelevant payment id';
    private $accessToken = 'irrelevant token';

    private $gopay;
    private $auth;
    private $api;

    protected function setUp()
    {
        $this->gopay = $this->prophesize('GoPay\GoPay');
        $this->auth = $this->prophesize('GoPay\Auth\OAuth2');
        $this->api = new Payments($this->gopay->reveal(), $this->auth->reveal());
    }

    /** @dataProvider provideApiMethods */
    public function testShouldCallApi($method, $params, $expectedRequest)
    {
        $this->auth->getAccessToken()->shouldBeCalled()->willReturn($this->accessToken);
        $this->gopay->call(
            $expectedRequest[0],
            $expectedRequest[1],
            $expectedRequest[2]
        )->shouldBeCalled();
        call_user_func_array(array($this->api, $method), $params);
    }

    public function provideApiMethods()
    {
        $jsonHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->accessToken}"
        ];
        $formHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => "Bearer {$this->accessToken}"
        ];
        return [
            'https://doc.gopay.com/en/#standard-payment' => [
                'createPayment',
                [['irrelevant payment']],
                [
                    'payments/payment',
                    $jsonHeaders,
                    ['irrelevant payment']
                ]
            ],
            'https://doc.gopay.com/en/#status-of-the-payment' => [
                'getStatus',
                [$this->id],
                [
                    "payments/payment/{$this->id}",
                    $formHeaders,
                    null
                ]
            ],
            'https://doc.gopay.com/en/#refund-of-the-payment-(cancelation)' => [
                'refund',
                [$this->id, 'amount'],
                [
                    "payments/payment/{$this->id}/refund",
                    $formHeaders,
                    ['amount' => 'amount']
                ]
            ],
            'https://doc.gopay.com/en/#recurring-payment' => [
                'createRecurrencePayment',
                [['irrelevant payment']],
                [
                    'payments/payment',
                    $jsonHeaders,
                    ['irrelevant payment']
                ]
            ],
            'https://doc.gopay.com/en/#recurring-payment-on-demand' => [
                'recurrenceOnDemand',
                [$this->id, ['irrelevant subsequent payment']],
                [
                    "payments/payment/{$this->id}/create-recurrence",
                    $jsonHeaders,
                    ['irrelevant subsequent payment']
                ]
            ],
            'https://doc.gopay.com/en/#cancellation-of-the-recurring-payment' => [
                'recurrenceVoid',
                [$this->id],
                [
                    "payments/payment/{$this->id}/void-recurrence",
                    $formHeaders,
                    []
                ]
            ],
            'https://doc.gopay.com/en/#pre-authorized-payment' => [
                'createPreauthorizedPayment',
                [['irrelevant payment']],
                [
                    'payments/payment',
                    $jsonHeaders,
                    ['irrelevant payment']
                ]
            ],
            'https://doc.gopay.com/en/#charge-of-pre-authorized-payment' => [
                'preauthorizedCapture',
                [$this->id],
                [
                    "payments/payment/{$this->id}/capture",
                    $formHeaders,
                    []
                ]
            ],
            'https://doc.gopay.com/en/#cancellation-of-the-pre-authorized-payment' => [
                'preauthorizedVoid',
                [$this->id],
                [
                    "payments/payment/{$this->id}/void-authorization",
                    $formHeaders,
                    []
                ]
            ],
        ];
    }
}
