<?php
namespace GoPay;

use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertStringContainsString;

class ProductionModeConfigurationTests extends TestCase
{
    /** @dataProvider provideConfig */
    public function testProductionConfigResolve($value, $expectedResult)
    {

        $browser = $this->getMockBuilder('GoPay\Http\JsonBrowser')
                ->disableOriginalConstructor()
                ->getMock();

        $gopay = new GoPay(['isProductionMode' => $value], $browser);
        $actualResult = $gopay->isProductionMode();
        assertEquals($expectedResult, $actualResult);
    }

    /** @dataProvider provideConfig2 */
    public function testProductionUrls($value, $url)
    {
        $browser = $this->getMockBuilder('GoPay\Http\JsonBrowser')
                ->disableOriginalConstructor()
                ->getMock();

        $gopay = new GoPay(['isProductionMode' => $value], $browser);
        $apiurl = $gopay->buildUrl("/");
        assertStringContainsString($url, $apiurl);
    }

    public function provideConfig2()
    {
        return [
            'prodUrl' => [ 'true', 'https://gate.gopay.cz/' ],
            'testUrl' => [ 'false', 'https://gw.sandbox.gopay.com/']
        ];
    }

    public function provideConfig()
    {
        return [
            'yes' => [ 'yes' , true ],
            'no' => [ 'no' , false ],
            '(bool) true' => [ true , true ],
            '(bool) false' => [ false , false ],
            '\'true\'' => [ 'true' , true ],
            '\'false\'' => [ 'false' , false ],
            'true' => [ "true" , true ],
            'false' => [ "false" , false ],
            'any other str' => [ "asdfawtgweqtqwt" , false ],
            '1' => [ 1 , true ],
            '0' => [ 0 , false ],
            '\"1\"' => [ "1" , true ],
            '\"0\"' => [ "0" , false ],
            'null' => [ null , false ],
        ];
    }
}