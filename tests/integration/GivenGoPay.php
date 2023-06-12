<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Definition\TokenScope;
use PHPUnit\Framework\TestCase;

class GivenGoPay extends TestCase
{
    /** @var Payments */
    private $gopay;
    /** @var Http\Response */
    protected $response;

    protected function givenCustomer(array $userConfig = [])
    {
        $config = $userConfig + [
            'goid' => getenv('goid'),
            'clientId' => getenv('clientId'),
            'clientSecret' => getenv('clientSecret'),
            'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
            'scope' => TokenScope::ALL,
            'language' => Language::CZECH
        ];
        $services = [
            'logger' => new Http\Log\PrintHttpRequest
        ];
        $this->gopay = payments($config, $services);
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
