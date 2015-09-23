<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Definition\TokenScope;

class RemoteApiTest extends \PHPUnit_Framework_TestCase
{
    /** @var Payments */
    private $gopay;
    /** @var Http\Response */
    private $response;

    /** @dataProvider provideLanguage */
    public function testErrorIsLocalized($language, $expectedError)
    {
        $this->givenCustomer([
            'clientSecret' => 'invalid secret',
            'language' => $language
        ]);
        $this->whenCustomerCalls('getStatus', 'irrelevant id is never used because token is not retrieved');
        $this->apiShouldReturnError(
            403,
            [
                'scope' => 'G',
                'field' => null,
                'error_code' => 202,
                'error_name' => 'AUTH_WRONG_CREDENTIALS',
                'message' => $expectedError,
                'description' => null
            ]
        );
    }

    public function provideLanguage()
    {
        return [
            [Language::CZECH, 'Chybné přihlašovací údaje. Pokuste se provést přihlášení znovu.'],
            [Language::RUSSIAN, 'Wrong credentials. Try sign in again.']
        ];
    }

    public function testStatusOfNonExistentPayment()
    {
        $nonExistentId = -100;
        $this->givenCustomer();
        $this->whenCustomerCalls('getStatus', $nonExistentId);
        $this->apiShouldReturnError(
            500,
            [
                'scope' => 'G',
                'field' => null,
                'error_code' => 500,
                'error_name' => null,
                'message' => null,
                'description' => null
            ]
        );
    }

    public function testCreatePaymentAndGetItsStatus()
    {
        $this->givenCustomer();
        $this->whenCustomerCalls('createPayment', [
            'amount' => 1,
            'currency' => Definition\Payment\Currency::CZECH_CROWNS,
            'order_number' => 'order-test - ' . date('Y-m-d H:i:s'),
            'order_description' => 'remote test',
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

    private function givenCustomer(array $userConfig = [])
    {
         $this->gopay = payments($userConfig + [
            'goid' => getenv('goid'),
            'clientId' => getenv('clientId'),
            'clientSecret' => getenv('clientSecret'),
            'isProductionMode' => false,
            'scope' => TokenScope::ALL,
            'language' => Language::CZECH
        ]);
    }

    private function whenCustomerCalls($method, $param)
    {
        $this->response = call_user_func([$this->gopay, $method], $param);
    }

    private function apiShouldReturn($field, $assert)
    {
        assertThat($this->response->hasSucceed(), is(true));
        assertThat($this->response->statusCode, is(200));
        assertThat($this->response->json[$field], $assert);
    }

    private function apiShouldReturnError($statusCode, $error)
    {
        assertThat($this->response->hasSucceed(), is(false));
        assertThat($this->response->statusCode, is($statusCode));
        assertThat($this->response->json['errors'][0], identicalTo($error));
    }
}
