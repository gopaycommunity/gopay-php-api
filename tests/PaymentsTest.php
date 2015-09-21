<?php

namespace GoPay;

class PaymentsTest extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'isProductionMode' => true
    ];
    private $id = 'irrelevant payment id';
    private $accessToken = 'irrelevant token';

    private $browser;
    private $auth;
    private $api;

    protected function setUp()
    {
        $this->browser = $this->prophesize('GoPay\Http\Browser');
        $this->auth = $this->prophesize('GoPay\OAuth2');
        $this->api = new Payments($this->config, $this->auth->reveal(), $this->browser->reveal());
    }

    /** @dataProvider provideApiMethods */
    public function testShouldCallApi($method, $params, $expectedRequest, $expectedMethod = 'postJson')
    {
        $this->browser->setBaseUrl($this->config['isProductionMode'])->shouldBeCalled();
        $this->auth->getAccessToken()->shouldBeCalled()->willReturn($this->accessToken);
        $this->browser->{$expectedMethod}(
            $expectedRequest[0],
            $expectedRequest[1],
            $expectedRequest[2]
        )->shouldBeCalled();
        call_user_func_array(array($this->api, $method), $params);
    }

    public function provideApiMethods()
    {
        return [
            'create payment' => [
                'createPayment',
                [['irrelevant payment']],
                [
                    'payments/payment',
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    ['irrelevant payment']
                ]
            ],
            'status of payment' => [
                'getStatus',
                [$this->id],
                [
                    "payments/payment/{$this->id}",
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    null
                ],
                'getJson'
            ],
            'refund payment' => [
                'refund',
                [$this->id, 'amount'],
                [
                    "payments/payment/{$this->id}/refund",
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    ['amount' => 'amount']
                ]
            ],
            'create recurrence payment' => [
                'createRecurrencePayment',
                [['irrelevant payment']],
                [
                    'payments/payment',
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    ['irrelevant payment']
                ]
            ],
            'create recurrence on demand' => [
                'recurrenceOnDemand',
                [$this->id, ['irrelevant subsequent payment']],
                [
                    "payments/payment/{$this->id}/create-recurrence",
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    ['irrelevant subsequent payment']
                ]
            ],
            'void recurrence' => [
                'recurrenceVoid',
                [$this->id],
                [
                    "payments/payment/{$this->id}/void-recurrence",
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Authorization' => "Bearer {$this->accessToken}"
                    ],
                    []
                ]
            ],
        ];
    }
}
