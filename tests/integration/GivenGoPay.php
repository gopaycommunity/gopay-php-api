<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Definition\TokenScope;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertIsArray;

class GivenGoPay extends TestCase
{
    private Payments $gopay;
    protected Http\Response $response;

    /**
     * @param array<string, mixed> $userConfig
     */
    protected function givenCustomer(array $userConfig = []): void
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

    protected function whenCustomerCalls(string $method, mixed ...$params): void
    {
        $this->response = $this->gopay->$method(...$params);
    }

    protected function apiShouldReturn(string $field, \Hamcrest\Matcher $assert): void
    {
        assertThat($this->response->hasSucceed(), is(true));
        assertThat($this->response->statusCode, is(200));
        assertIsArray($this->response->json);
        assertThat($this->response->json[$field], $assert);
    }

    /**
     * @param array<string, mixed> $error
     */
    protected function apiShouldReturnError(int $statusCode, array $error): void
    {
        assertThat($this->response->hasSucceed(), is(false));
        assertThat($this->response->statusCode, is($statusCode));
        assertIsArray($this->response->json);
        assertThat($this->response->json['errors'][0], identicalTo($error));
    }
}
