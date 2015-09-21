<?php

namespace GoPay;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider provideStatusCode */
    public function testWhenStatusIs($statusCode, $hasSucceed)
    {
        $response = new Response;
        $response->statusCode = $statusCode;
        assertThat($response->hasSucceed(), is($hasSucceed));
    }

    public function provideStatusCode()
    {
        return [
            'success' => [200, true],
            'failure' => [409, false]
        ];
    }
}
