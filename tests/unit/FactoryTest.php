<?php

namespace GoPay;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testTravisShouldFail()
    {
        assertThat(1+1, is(3));
    }

    /** @dataProvider provideFactoryMethod */
    public function testShouldBuildPayments($method)
    {
        $payments = call_user_func($method, ['irrelevant config']);
        assertThat($payments, anInstanceOf('GoPay\Payments'));
    }
    
    public function provideFactoryMethod()
    {
        return [
            'function' => ['GoPay\payments'],
            'class' => [['GoPay\Api', 'payments']],
        ];
    }
}
