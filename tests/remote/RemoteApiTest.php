<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Definition\TokenScope;

class RemoteApiTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider provideLanguage */
    public function testErrorIsLocalized($language, $expectedError)
    {
        $gopay = $this->givenGopay([
            'clientSecret' => 'invalid secret',
            'language' => $language
        ]);
        $response = $gopay->getStatus('payment id');
        assertThat($response->hasSucceed(), is(false));
        assertThat($response->statusCode, is(403));
        assertThat($response->json['errors'][0], identicalTo([
            'scope' => 'G',
            'field' => null,
            'error_code' => 202,
            'error_name' => 'AUTH_WRONG_CREDENTIALS',
            'message' => $expectedError,
            'description' => null
        ]));
    }

    public function provideLanguage()
    {
        return [
            [Language::CZECH, 'Chybné přihlašovací údaje. Pokuste se provést přihlášení znovu.'],
            [Language::RUSSIAN, 'Wrong credentials. Try sign in again.']
        ];
    }

    private function givenGopay(array $userConfig = [])
    {
        return payments($userConfig + [
            'goid' => getenv('goid'),
            'clientId' => getenv('clientId'),
            'clientSecret' => getenv('clientSecret'),
            'isProductionMode' => false,
            'scope' => TokenScope::ALL,
            'language' => Language::CZECH
        ]);
    }
}
