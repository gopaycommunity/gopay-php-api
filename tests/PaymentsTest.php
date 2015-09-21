<?php

namespace GoPay;

class PaymentsTest extends \PHPUnit_Framework_TestCase
{
    public function testEnv()
    {
        assertThat(new Payments, anInstanceOf('GoPay\Payments'));
    }
}
