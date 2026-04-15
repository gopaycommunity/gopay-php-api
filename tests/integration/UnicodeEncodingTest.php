<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Http\JsonBrowser;
use GoPay\Http\Request;
use GoPay\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for JSON_UNESCAPED_UNICODE fix in GoPay::encodeData().
 *
 * Background: PHP's json_encode() without flags escapes non-ASCII Unicode
 * characters as \uXXXX sequences (e.g. Cyrillic "СЛИВЕН" becomes
 * "\u0421\u041b\u0418\u0412\u0415\u041d"). The GoPay gateway WAF was
 * incorrectly treating these sequences as SQL Injection and returning HTTP 403.
 * Fix: JSON_UNESCAPED_UNICODE flag keeps UTF-8 characters as-is.
 *
 * @see GoPay::encodeData()
 */
class UnicodeEncodingTest extends TestCase
{
    /** Minimal config sufficient to construct a GoPay instance (no real network needed). */
    private function makeConfig(): array
    {
        return [
            'goid'          => '0',
            'clientId'      => 'test',
            'clientSecret'  => 'secret',
            'gatewayUrl'    => 'https://gw.sandbox.gopay.com/api',
            'language'      => Language::CZECH,
            'scope'         => 'payment-all',
            'timeout'       => 30,
        ];
    }

    /**
     * Creates a GoPay instance with a mock JsonBrowser that captures the last
     * outgoing Request body so we can inspect it.
     *
     * @return array{GoPay, \stdClass} [$gopay, $capture] – $capture->body is set after call()
     */
    private function makeGoPayWithCapture(): array
    {
        $capture = new \stdClass();
        $capture->body = null;

        $browser = $this->createMock(JsonBrowser::class);
        $browser->method('send')->willReturnCallback(function (Request $r) use ($capture) {
            $capture->body = $r->body;
            $resp = new Response('{"id":"mock"}');
            $resp->statusCode = 200;
            $resp->json = ['id' => 'mock'];
            return $resp;
        });

        $gopay = new GoPay($this->makeConfig(), $browser);

        return [$gopay, $capture];
    }

    /**
     * Helper: encode $data through the full call() stack and return the captured body string.
     */
    private function encodeViaCall(array $data, string $contentType = GoPay::JSON): string
    {
        [$gopay, $capture] = $this->makeGoPayWithCapture();
        $gopay->call('/payments/payment', 'Bearer mock', 'POST', $contentType, $data);
        return (string) $capture->body;
    }

    // -------------------------------------------------------------------------
    // 1. Direct unit tests of encodeData() via call() stack
    // -------------------------------------------------------------------------

    /**
     * Cyrillic characters must NOT be escaped as \uXXXX in JSON output.
     *
     * Reproduces the exact customer issue: Bulgarian item names like
     * "СЛИВЕН - РЕЧИЦА (АВТОМАТ)" were escaped and flagged as SQL Injection.
     */
    public function testCyrillicIsNotEscaped(): void
    {
        $result = $this->encodeViaCall(['name' => 'СЛИВЕН - РЕЧИЦА (АВТОМАТ)']);

        $this->assertStringNotContainsString(
            '\u',
            $result,
            'Cyrillic characters must not be \\uXXXX-escaped in the JSON body'
        );
        $this->assertStringContainsString(
            'СЛИВЕН - РЕЧИЦА (АВТОМАТ)',
            $result,
            'Cyrillic characters must appear as raw UTF-8 in the JSON body'
        );
    }

    /**
     * Bulgarian (Cyrillic) characters must be present as UTF-8 in encoded output.
     */
    public function testBulgarianCyrillicRemainsUtf8(): void
    {
        $bulgarianNames = [
            'СЛИВЕН - РЕЧИЦА (АВТОМАТ)',
            'СОФИЯ - ЦЕНТРАЛНА ГАРА',
            'ПЛОВДИВ - ТРИМОНЦИУМ',
        ];

        foreach ($bulgarianNames as $name) {
            $result = $this->encodeViaCall(['item_name' => $name]);

            $this->assertStringNotContainsString('\u', $result,
                "Item name '$name' must not contain \\uXXXX escape sequences");
            $this->assertStringContainsString($name, $result,
                "Item name '$name' must appear verbatim in JSON");
        }
    }

    /**
     * Other non-ASCII scripts (Greek, Hebrew, Arabic, Chinese, Czech diacritics)
     * must also remain unescaped.
     */
    public function testOtherNonAsciiScriptsAreNotEscaped(): void
    {
        $samples = [
            'greek'   => 'Αθήνα',
            'arabic'  => 'القاهرة',
            'chinese' => '北京',
            'czech'   => 'Příliš žluťoučký kůň',
            'hebrew'  => 'ירושלים',
        ];

        foreach ($samples as $script => $text) {
            $result = $this->encodeViaCall(['city' => $text]);

            $this->assertStringNotContainsString('\u', $result,
                "$script text must not contain \\uXXXX escapes in JSON");
            $this->assertStringContainsString($text, $result,
                "$script text must appear verbatim in JSON");
        }
    }

    /**
     * Plain ASCII data must still encode correctly (regression guard).
     */
    public function testAsciiDataEncodesCorrectly(): void
    {
        $result = $this->encodeViaCall(['name' => 'item01', 'amount' => 2300]);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertSame('item01', $decoded['name']);
        $this->assertSame(2300, $decoded['amount']);
    }

    /**
     * FORM content-type must still use http_build_query (no JSON encoding).
     */
    public function testFormEncodingIsUnaffected(): void
    {
        $result = $this->encodeViaCall(
            ['grant_type' => 'client_credentials', 'scope' => 'payment-all'],
            GoPay::FORM
        );

        $this->assertStringContainsString('grant_type=client_credentials', $result);
        $this->assertStringNotContainsString('{', $result, 'FORM encoding must not produce JSON');
    }

    /**
     * Empty array data must return an empty string (no encoding attempted).
     */
    public function testEmptyDataReturnsEmptyString(): void
    {
        [$gopay, $capture] = $this->makeGoPayWithCapture();
        // Pass null explicitly via call() – body should be ''
        $gopay->call('/payments/payment', 'Bearer mock', 'POST', GoPay::JSON, null);
        $this->assertSame('', $capture->body);
    }

    // -------------------------------------------------------------------------
    // 2. End-to-end through call() – verifies the fix works in the full stack
    // -------------------------------------------------------------------------

    /**
     * When a payment with Cyrillic item names is submitted via call(), the
     * captured HTTP request body must not contain \uXXXX escape sequences.
     *
     * This is the exact scenario that caused HTTP 403 for O-global s.r.o.
     * (EVČ: 1878275146) when ordering from www.orsay.com/bg.
     */
    public function testCallWithCyrillicItemNamesDoesNotEscapeUnicode(): void
    {
        [$gopay, $capture] = $this->makeGoPayWithCapture();

        $payload = [
            'payer'   => ['contact' => ['email' => 'test@example.com']],
            'amount'  => 10000,
            'currency' => 'CZK',
            'order_number' => 'BG-001',
            'items'   => [
                ['name' => 'СЛИВЕН - РЕЧИЦА (АВТОМАТ)', 'amount' => 5000, 'count' => 1],
                ['name' => 'СОФИЯ - ЦЕНТРАЛНА ГАРА',    'amount' => 5000, 'count' => 1],
            ],
            'callback' => [
                'return_url'       => 'https://orsay.com/bg/return',
                'notification_url' => 'https://orsay.com/bg/notify',
            ],
        ];

        $gopay->call(
            '/payments/payment',
            'Bearer mock-token',
            'POST',
            GoPay::JSON,
            $payload
        );

        $this->assertNotNull($capture->body, 'Request body must have been captured');
        $this->assertStringNotContainsString(
            '\u',
            $capture->body,
            'The outgoing JSON body must not contain \\uXXXX escape sequences – ' .
            'they trigger WAF SQL Injection protection on the GoPay gateway (HTTP 403)'
        );
        $this->assertStringContainsString('СЛИВЕН - РЕЧИЦА (АВТОМАТ)', $capture->body);
        $this->assertStringContainsString('СОФИЯ - ЦЕНТРАЛНА ГАРА',    $capture->body);
    }

    /**
     * Mixed payload: Latin + Cyrillic + Czech diacritics in the same request.
     */
    public function testCallWithMixedUnicodePayload(): void
    {
        [$gopay, $capture] = $this->makeGoPayWithCapture();

        $payload = [
            'order_number' => 'MIX-001',
            'amount'       => 9900,
            'currency'     => 'CZK',
            'items'        => [
                ['name' => 'Příliš žluťoučký kůň',       'amount' => 3300, 'count' => 1],
                ['name' => 'СЛИВЕН - РЕЧИЦА (АВТОМАТ)',   'amount' => 3300, 'count' => 1],
                ['name' => 'Standard ASCII item',         'amount' => 3300, 'count' => 1],
            ],
            'callback' => [
                'return_url'       => 'https://example.com/return',
                'notification_url' => 'https://example.com/notify',
            ],
        ];

        $gopay->call('/payments/payment', 'Bearer mock-token', 'POST', GoPay::JSON, $payload);

        $this->assertStringNotContainsString('\u', $capture->body,
            'Mixed Unicode payload must not contain \\uXXXX escapes');
        $this->assertStringContainsString('Příliš žluťoučký kůň',       $capture->body);
        $this->assertStringContainsString('СЛИВЕН - РЕЧИЦА (АВТОМАТ)',   $capture->body);
        $this->assertStringContainsString('Standard ASCII item',         $capture->body);
    }
}
