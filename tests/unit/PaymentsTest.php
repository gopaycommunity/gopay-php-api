<?php

namespace GoPay;

use GoPay\Token\AccessToken;
use GoPay\Definition\Language;

class PaymentsTest extends \PHPUnit_Framework_TestCase
{
    private $id = 'irrelevant payment id';
    private $accessToken = 'irrelevant token';
    private $config = [
        'language' => Language::CZECH,
        'goid' => 'irrelevant id'
    ];

    private $gopay;
    private $auth;
    private $api;

    protected function setUp()
    {
        $this->gopay = $this->prophesize('GoPay\GoPay');
        foreach ($this->config as $key => $value) {
            $this->gopay->getConfig($key)->willReturn($value);
        }
        $this->auth = $this->prophesize('GoPay\OAuth2');
        $this->api = new Payments($this->gopay->reveal(), $this->auth->reveal());
    }

    public function testShouldReturnTokenResponseWhenTokenIsNotLoaded()
    {
        $token = $this->givenAccessToken('');
        $token->response = 'irrelevant response (instanceof Response)';
        $response = $this->api->getStatus('irrelevant id');
        assertThat($response, identicalTo($token->response));
    }

    /** @dataProvider provideApiMethods */
    public function testShouldCallApi($method, $params, $expectedRequest)
    {
        $this->givenAccessToken($this->accessToken);
        $this->gopay->call(
            $expectedRequest[0],
            $expectedRequest[1],
            "Bearer {$this->accessToken}",
            $expectedRequest[2]
        )->shouldBeCalled();
        call_user_func_array(array($this->api, $method), $params);
    }

    public function provideApiMethods()
    {
        return [
            'https://doc.gopay.com/en/#standard-payment - add default language' => [
                'createPayment',
                [['irrelevant payment']],
                [
                    'payments/payment',
                    GoPay::JSON,
                    [
                        'irrelevant payment',
                        'lang' => $this->config['language'],
                        'target' => [
                            'type' => 'ACCOUNT',
                            'goid' => $this->config['goid']
                        ]
                    ]
                ]
            ],
            'create payment - do not override parameters' => [
                'createPayment',
                [['irrelevant payment', 'lang' => 'invalid-lang', 'target' => 'invalid-target']],
                [
                    'payments/payment',
                    GoPay::JSON,
                    ['irrelevant payment', 'lang' => 'invalid-lang', 'target' => 'invalid-target']
                ]
            ],
            'https://doc.gopay.com/en/#status-of-the-payment' => [
                'getStatus',
                [$this->id],
                [
                    "payments/payment/{$this->id}",
                    GoPay::FORM,
                    null
                ]
            ],
            'https://doc.gopay.com/en/#refund-of-the-payment-(cancelation)' => [
                'refund',
                [$this->id, 'amount'],
                [
                    "payments/payment/{$this->id}/refund",
                    GoPay::FORM,
                    ['amount' => 'amount']
                ]
            ],
            'https://doc.gopay.com/en/#recurring-payment-on-demand' => [
                'recurrenceOnDemand',
                [$this->id, ['irrelevant subsequent payment']],
                [
                    "payments/payment/{$this->id}/create-recurrence",
                    GoPay::JSON,
                    ['irrelevant subsequent payment']
                ]
            ],
            'https://doc.gopay.com/en/#cancellation-of-the-recurring-payment' => [
                'recurrenceVoid',
                [$this->id],
                [
                    "payments/payment/{$this->id}/void-recurrence",
                    GoPay::FORM,
                    []
                ]
            ],
            'https://doc.gopay.com/en/#charge-of-pre-authorized-payment' => [
                'preauthorizedCapture',
                [$this->id],
                [
                    "payments/payment/{$this->id}/capture",
                    GoPay::FORM,
                    []
                ]
            ],
            'https://doc.gopay.com/en/#cancellation-of-the-pre-authorized-payment' => [
                'preauthorizedVoid',
                [$this->id],
                [
                    "payments/payment/{$this->id}/void-authorization",
                    GoPay::FORM,
                    []
                ]
            ],
        ];
    }

    private function givenAccessToken($token)
    {
        $t = new AccessToken;
        $t->token = $token;
        $this->auth->getAccessToken()->shouldBeCalled()->willReturn($t);
        return $t;
    }

    public function testShouldReturnEmbedJs()
    {
        $this->gopay->buildUrl('gp-gw/js/embed.js')->shouldBeCalled();
        $this->api->urlToEmbedJs();
    }
}
