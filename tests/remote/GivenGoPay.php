<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Definition\TokenScope;

class GivenGoPay extends \PHPUnit_Framework_TestCase
{
    /** @var Payments */
    private $gopay;
    /** @var Http\Response */
    protected $response;

    protected function givenCustomer(array $userConfig = [])
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

    protected function whenCustomerCalls()
    {
        $params = func_get_args();
        $method = array_shift($params);
        $this->response = call_user_func_array([$this->gopay, $method], $params);
    }

    protected function apiShouldReturn($field, $assert)
    {
        assertThat($this->response->hasSucceed(), is(true));
        assertThat($this->response->statusCode, is(200));
        assertThat($this->response->json[$field], $assert);
    }

    protected function apiShouldReturnError($statusCode, $error)
    {
        assertThat($this->response->hasSucceed(), is(false));
        assertThat($this->response->statusCode, is($statusCode));
        assertThat($this->response->json['errors'][0], identicalTo($error));
    }
}
