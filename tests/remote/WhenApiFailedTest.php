<?php

namespace GoPay;

use GoPay\Definition\Language;

class WhenApiFailedTest extends GivenGoPay
{
    /** @dataProvider provideLanguage */
    public function testErrorMessageIsLocalized($language, $expectedError)
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
}
