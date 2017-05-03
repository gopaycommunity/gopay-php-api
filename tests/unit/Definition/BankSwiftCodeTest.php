<?php

namespace GoPay\Definition\Payment;

class BankSwiftCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testSwiftCodeHasEightOrTwelveCharacters()
    {
        $reflection = new \ReflectionClass('GoPay\Definition\Payment\BankSwiftCode');
        foreach ($reflection->getConstants() as $code) {
            if ($code != "OTHERS") {
                $this->assertSwiftCodeLength($code);
            }
        }
    }

    private function assertSwiftCodeLength($code)
    {
        $expectedLength = $code == BankSwiftCode::ERA ? 12 : 8;
        assertThat(strlen($code), is($expectedLength));
    }
}
