<?php

namespace GoPay\Tests\Unit;

use GoPay\Auth;
use GoPay\Definition\Language;
use GoPay\Definition\RequestMethods;
use GoPay\GoPay;
use GoPay\Http\Response;
use GoPay\Payments;
use GoPay\Token\AccessToken;
use PHPUnit\Framework\TestCase;

class PaymentsTest extends TestCase
{
    private RecordingBrowser $browser;
    private Payments $payments;

    protected function setUp(): void
    {
        $this->browser = new RecordingBrowser();
        $this->payments = $this->payments($this->browser, self::authReturning(self::validToken()));
    }

    private function payments(RecordingBrowser $browser, Auth $auth): Payments
    {
        $gopay = new GoPay(
            [
                'goid' => '8583067438',
                'clientId' => 'client-id',
                'clientSecret' => 'client-secret',
                'gatewayUrl' => 'https://gw.sandbox.gopay.com/api',
                'language' => Language::CZECH,
            ],
            $browser
        );
        return new Payments($gopay, $auth);
    }

    private static function validToken(): AccessToken
    {
        $token = new AccessToken();
        $token->token = 'test-token';
        $token->expirationDate = new \DateTime('+30 minutes');
        return $token;
    }

    private static function authReturning(AccessToken $token): Auth
    {
        return new class ($token) implements Auth {
            public function __construct(private AccessToken $token) {}

            public function authorize()
            {
                return $this->token;
            }
        };
    }

    /**
     * @return array<string, array{string, list<mixed>, string, string, string|null, string}>
     */
    public static function provideEndpoints(): array
    {
        return [
            'getStatus' => ['getStatus', [123], RequestMethods::GET, '/payments/payment/123', null, ''],
            'getQrPayment' => ['getQrPayment', [123], RequestMethods::GET, '/payments/payment/123/qr-payment', null, ''],
            'getQrPayment with format' => ['getQrPayment', [123, 'svg'], RequestMethods::GET, '/payments/payment/123/qr-payment?format=svg', null, ''],
            'refundPayment with amount' => ['refundPayment', [123, 500], RequestMethods::POST, '/payments/payment/123/refund', GoPay::FORM, 'amount=500'],
            'refundPayment with array' => ['refundPayment', [123, ['amount' => 500]], RequestMethods::POST, '/payments/payment/123/refund', GoPay::JSON, '{"amount":500}'],
            'refundPaymentEET' => ['refundPaymentEET', [123, ['amount' => 500]], RequestMethods::POST, '/payments/payment/123/refund', GoPay::JSON, '{"amount":500}'],
            'createRecurrence' => ['createRecurrence', [123, ['amount' => 500]], RequestMethods::POST, '/payments/payment/123/create-recurrence', GoPay::JSON, '{"amount":500}'],
            'voidRecurrence' => ['voidRecurrence', [123], RequestMethods::POST, '/payments/payment/123/void-recurrence', GoPay::FORM, ''],
            'captureAuthorization' => ['captureAuthorization', [123], RequestMethods::POST, '/payments/payment/123/capture', GoPay::FORM, ''],
            'captureAuthorizationPartial' => ['captureAuthorizationPartial', [123, ['amount' => 500]], RequestMethods::POST, '/payments/payment/123/capture', GoPay::JSON, '{"amount":500}'],
            'voidAuthorization' => ['voidAuthorization', [123], RequestMethods::POST, '/payments/payment/123/void-authorization', GoPay::FORM, ''],
            'getCardDetails' => ['getCardDetails', [456], RequestMethods::GET, '/payments/cards/456', null, ''],
            'deleteCard' => ['deleteCard', [456], RequestMethods::DELETE, '/payments/cards/456', null, ''],
            'getHistoryRefunds' => ['getHistoryRefunds', [123], RequestMethods::GET, '/payments/payment/123/refunds', null, ''],
            'getPaymentInstruments' => ['getPaymentInstruments', [789, 'CZK'], RequestMethods::GET, '/eshops/eshop/789/payment-instruments/CZK', null, ''],
            'getPaymentInstrumentsAll' => ['getPaymentInstrumentsAll', [789], RequestMethods::GET, '/eshops/eshop/789/payment-instruments', null, ''],
            'getAccountStatement' => ['getAccountStatement', [['goid' => 789]], RequestMethods::POST, '/accounts/account-statement', GoPay::JSON, '{"goid":789}'],
            'getEETReceiptByPaymentId' => ['getEETReceiptByPaymentId', [123], RequestMethods::GET, '/payments/payment/123/eet-receipts', null, ''],
            'findEETReceiptsByFilter' => ['findEETReceiptsByFilter', [['id_provozovny' => 11]], RequestMethods::POST, '/eet-receipts', GoPay::JSON, '{"id_provozovny":11}'],
        ];
    }

    /**
     * @dataProvider provideEndpoints
     *
     * @param list<mixed> $args
     */
    public function testEndpointSendsExpectedRequest(
        string $method,
        array $args,
        string $httpMethod,
        string $path,
        ?string $contentType,
        string $body,
    ): void {
        $callable = [$this->payments, $method];
        $this->assertIsCallable($callable);
        $response = $callable(...$args);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(1, $this->browser->requests);
        $request = $this->browser->lastRequest();
        $this->assertSame($httpMethod, $request->method);
        $this->assertSame('https://gw.sandbox.gopay.com/api' . $path, $request->url);
        $this->assertSame('Bearer test-token', $request->headers['Authorization']);
        $this->assertSame($contentType, $request->headers['Content-Type'] ?? null);
        $this->assertSame($body, $request->body);
    }

    public function testCreatePaymentAddsDefaultTargetAndLanguage(): void
    {
        $this->payments->createPayment(['amount' => 100]);

        $request = $this->browser->lastRequest();
        $this->assertSame(RequestMethods::POST, $request->method);
        $this->assertSame('https://gw.sandbox.gopay.com/api/payments/payment', $request->url);
        $this->assertSame(GoPay::JSON, $request->headers['Content-Type']);
        $this->assertSame(
            ['amount' => 100, 'target' => ['type' => 'ACCOUNT', 'goid' => '8583067438'], 'lang' => Language::CZECH],
            json_decode((string) $request->body, true)
        );
    }

    public function testCreatePaymentKeepsProvidedTargetAndLanguage(): void
    {
        $payment = ['amount' => 100, 'target' => ['type' => 'ACCOUNT', 'goid' => '111'], 'lang' => Language::GERMAN];

        $this->payments->createPayment($payment);

        $this->assertSame($payment, json_decode((string) $this->browser->lastRequest()->body, true));
    }

    public function testResponseFromBrowserIsReturned(): void
    {
        $response = RecordingBrowser::response(200, ['id' => 123]);
        $payments = $this->payments(new RecordingBrowser($response), self::authReturning(self::validToken()));

        $this->assertSame($response, $payments->getStatus(123));
    }

    /**
     * @return array<string, array{string, list<mixed>}>
     */
    public static function provideHttpMethods(): array
    {
        return [
            'GET' => ['getStatus', [123]],
            'POST' => ['createPayment', [[]]],
            'DELETE' => ['deleteCard', [456]],
        ];
    }

    /**
     * @dataProvider provideHttpMethods
     *
     * @param list<mixed> $args
     */
    public function testOAuthErrorIsReturnedWithoutCallingEndpoint(string $method, array $args): void
    {
        $oauthError = RecordingBrowser::response(403, ['errors' => [['error_name' => 'AUTH_WRONG_CREDENTIALS']]]);
        $failedToken = new AccessToken();
        $failedToken->response = $oauthError;
        $browser = new RecordingBrowser();
        $payments = $this->payments($browser, self::authReturning($failedToken));

        $callable = [$payments, $method];
        $this->assertIsCallable($callable);
        $response = $callable(...$args);

        $this->assertSame($oauthError, $response);
        $this->assertCount(0, $browser->requests);
    }

    public function testUrlToEmbedJs(): void
    {
        $this->assertSame('https://gw.sandbox.gopay.com/gp-gw/js/embed.js', $this->payments->urlToEmbedJs());
    }
}
