<?php

namespace GoPay\Tests\Unit;

use GoPay\Config;
use GoPay\Definition\Language;
use GoPay\Definition\RequestMethods;
use GoPay\GoPay;
use PHPUnit\Framework\TestCase;

class GoPayTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     */
    private function gopay(array $config = [], ?RecordingBrowser $browser = null): GoPay
    {
        return new GoPay(
            $config + [
                'goid' => '8583067438',
                'clientId' => 'client-id',
                'clientSecret' => 'client-secret',
                'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
                'language' => Language::ENGLISH,
            ],
            $browser ?? new RecordingBrowser()
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideGatewayUrls(): array
    {
        return [
            'with /api' => ['https://gw.sandbox.gopay.com/api'],
            'with /api/' => ['https://gw.sandbox.gopay.com/api/'],
            'without /api' => ['https://gw.sandbox.gopay.com'],
            'without /api, trailing slash' => ['https://gw.sandbox.gopay.com/'],
        ];
    }

    /**
     * @dataProvider provideGatewayUrls
     */
    public function testBuildUrlAppendsApiOnlyWhenMissing(string $gatewayUrl): void
    {
        $gopay = $this->gopay(['gatewayUrl' => $gatewayUrl]);

        $this->assertSame(
            'https://gw.sandbox.gopay.com/api/payments/payment/123',
            $gopay->buildUrl('/payments/payment/123')
        );
    }

    /**
     * @dataProvider provideGatewayUrls
     */
    public function testBuildEmbedUrlStripsApi(string $gatewayUrl): void
    {
        $gopay = $this->gopay(['gatewayUrl' => $gatewayUrl]);

        $this->assertSame('https://gw.sandbox.gopay.com/gp-gw/js/embed.js', $gopay->buildEmbedUrl());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideLanguages(): array
    {
        return [
            'czech' => [Language::CZECH, 'cs-CZ'],
            'slovak' => [Language::SLOVAK, 'cs-CZ'],
            'english' => [Language::ENGLISH, 'en-US'],
            'german' => [Language::GERMAN, 'en-US'],
        ];
    }

    /**
     * @dataProvider provideLanguages
     */
    public function testAcceptLanguageDependsOnConfiguredLanguage(string $language, string $expected): void
    {
        $browser = new RecordingBrowser();
        $this->gopay(['language' => $language], $browser)->call('/x', 'Bearer t', RequestMethods::GET);

        $this->assertSame($expected, $browser->lastRequest()->headers['Accept-Language']);
    }

    public function testCallBuildsRequestWithoutBody(): void
    {
        $browser = new RecordingBrowser();
        $this->gopay([], $browser)->call('/payments/payment/123', 'Bearer t', RequestMethods::GET);

        $request = $browser->lastRequest();
        $this->assertSame(RequestMethods::GET, $request->method);
        $this->assertSame('https://gw.sandbox.gopay.com/api/payments/payment/123', $request->url);
        $this->assertSame('', $request->body);
        $this->assertSame(
            [
                'Accept' => 'application/json',
                'Accept-Language' => 'en-US',
                'Authorization' => 'Bearer t',
                'User-Agent' => GoPay::DEFAULT_USER_AGENT,
            ],
            $request->headers
        );
    }

    public function testCallWithJsonContentType(): void
    {
        $browser = new RecordingBrowser();
        $this->gopay([], $browser)->call('/x', 'Bearer t', RequestMethods::POST, GoPay::JSON, ['amount' => 100]);

        $request = $browser->lastRequest();
        $this->assertSame(GoPay::JSON, $request->headers['Content-Type']);
        $this->assertSame('{"amount":100}', $request->body);
    }

    public function testCallWithFormContentType(): void
    {
        $browser = new RecordingBrowser();
        $this->gopay([], $browser)->call('/x', 'Bearer t', RequestMethods::POST, GoPay::FORM, ['amount' => 100, 'a' => 'b c']);

        $request = $browser->lastRequest();
        $this->assertSame(GoPay::FORM, $request->headers['Content-Type']);
        $this->assertSame('amount=100&a=b+c', $request->body);
    }

    public function testCallReturnsBrowserResponse(): void
    {
        $response = RecordingBrowser::response(404, ['errors' => []]);

        $result = $this->gopay([], new RecordingBrowser($response))->call('/x', 'Bearer t', RequestMethods::GET);

        $this->assertSame($response, $result);
    }

    public function testCustomUserAgentOverridesDefault(): void
    {
        $browser = new RecordingBrowser();
        $this->gopay(['customUserAgent' => 'My Shop 1.0'], $browser)->call('/x', 'Bearer t', RequestMethods::GET);

        $this->assertSame('My Shop 1.0', $browser->lastRequest()->headers['User-Agent']);
    }

    public function testGetConfigReturnsValueFromArrayConfig(): void
    {
        $gopay = $this->gopay(['goid' => '123']);

        $this->assertSame('123', $gopay->getConfig('goid'));
    }

    public function testConfigObjectIsAccepted(): void
    {
        $config = new Config('client-id', 'client-secret', '123', 'https://gw.sandbox.gopay.com');
        $config->language = Language::SLOVAK;
        $browser = new RecordingBrowser();

        $gopay = new GoPay($config, $browser);
        $gopay->call('/x', 'Bearer t', RequestMethods::GET);

        $this->assertSame('123', $gopay->getConfig('goid'));
        $this->assertSame(30, $gopay->getConfig('timeout'));
        $this->assertSame('https://gw.sandbox.gopay.com/api/x', $browser->lastRequest()->url);
        $this->assertSame('cs-CZ', $browser->lastRequest()->headers['Accept-Language']);
    }
}
