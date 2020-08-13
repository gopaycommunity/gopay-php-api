<?php

namespace GoPay;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertInstanceOf;

class FactoryTest extends TestCase
{
    /** @dataProvider provideFactoryMethod */
    public function testShouldBuildPayments($method)
    {
        $payments = call_user_func($method, ['irrelevant config']);
        assertInstanceOf('GoPay\Payments', $payments);
    }
    
    public function provideFactoryMethod()
    {
        return [
            'function' => ['GoPay\payments'],
            'class' => [['GoPay\Api', 'payments']],
        ];
    }
}
