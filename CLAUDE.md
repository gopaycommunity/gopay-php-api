# CLAUDE.md

## Project

`gopay/payments-sdk-php` — a thin PHP (>= 8.1) SDK over the GoPay Payments REST API (https://doc.gopay.com). Uses Guzzle 7. Published on Packagist; the SDK version lives in `GoPay::VERSION` in [src/GoPay.php](src/GoPay.php) and is sent in the default `User-Agent`.

## Commands

```bash
composer install                                   # composer bin-dir is ./bin, so tools land in bin/
bin/phpunit                                        # run all tests (same as CI, uses phpunit.xml.dist)
bin/phpunit tests/integration/RefundTest.php       # single test file
bin/phpunit --filter testRefundPayment tests/integration   # single test method
composer validate --strict                         # CI also runs this
composer phpstan                                   # PHPStan on src + tests (config phpstan.neon.dist, level 8), CI also runs this
composer cs-check                                  # PHP-CS-Fixer dry run with diff (config .php-cs-fixer.dist.php), CI also runs this
composer cs-fix                                    # apply coding style fixes
bin/captainhook install -f                         # (re)install git hooks; normally done automatically by composer install
```

Git hooks are managed by CaptainHook ([captainhook.json](captainhook.json)), installed into `.git/hooks` by the `captainhook/hook-installer` Composer plugin. The `pre-commit` hook runs on staged files only: PHP-CS-Fixer dry run and PHPStan on staged `*.php`, `composer validate --strict` when `composer.json`/`composer.lock` is staged. Integration tests are intentionally not in the hook (network, slow). Tools are invoked as `php bin/...`, so `php` in `PATH` must be >= 8.1. Bypass with `git commit --no-verify`.

PHPStan rules for this repo: public API (public/protected methods, public properties, interfaces) is typed **only via PHPDoc** to keep BC; native types only on private members. Legacy unreachable code is ignored inline with `@phpstan-ignore <identifier>` plus a reason, no baseline. The Jenkins script [bin/jenkins.sh](bin/jenkins.sh) runs `phpqa` if available. `phpunit.xml.dist` is used by CI and makes deprecations, warnings and risky tests fail the run (PHPUnit 9 can enable deprecation failures only via XML, so don't run CI with `--no-configuration`); only `tests/integration/` exists.

### Tests hit the real sandbox

All tests are integration tests against `https://gw.sandbox.gopay.com/api` and need network access. Sandbox credentials are hardcoded in [tests/integration/TestUtils.php](tests/integration/TestUtils.php) (`TestUtils::setup()` builds a `Payments` instance; there is a separate EET account). Many tests require IDs of existing sandbox payments/cards (test files note that a method only runs if its name has the `test` prefix). `QrPaymentTest.php.disabled` is disabled on purpose. Tests use Hamcrest (`assertThat`, `is(...)`) loaded via `autoload-dev`, plus PHPUnit asserts.

## Architecture

Object graph is wired in [factory.php](factory.php) (autoloaded as a file), via `GoPay\payments($config, $services)` / `GoPay\Api::payments(...)`:

```
Payments  (public API: createPayment, getStatus, refundPayment, ...)
 ├─ GoPay          – builds URL/headers, encodes body, delegates to JsonBrowser
 │   └─ JsonBrowser – Guzzle wrapper; ALWAYS returns Http\Response, never throws
 └─ CachedOAuth (Auth) – wraps OAuth2 + TokenCache (default InMemoryTokenCache)
```

Key behaviors spread across files:

- **Adding an endpoint** = one method in [src/Payments.php](src/Payments.php) calling `$this->get/post/delete($path, ...)`. Those helpers obtain a token via `$this->auth->authorize()`; if token acquisition failed they return the OAuth error `Response` instead of calling the endpoint.
- **Content type matters per endpoint**: `GoPay::FORM` (urlencoded via `http_build_query`) vs `GoPay::JSON`. Simple actions (refund by amount, capture, void) use FORM; payloads use JSON. JSON is encoded with `JSON_UNESCAPED_UNICODE` deliberately — escaped `\uXXXX` sequences trigger the gateway WAF (403). Don't remove it.
- **No exceptions, no validation**: by design the SDK does not validate input (the API does) and `JsonBrowser` converts all errors (Guzzle `ClientException` → API status/body; anything else → status 500 with the message as body). Callers check `Response::hasSucceed()` (status < 400), `->json`, `->statusCode` (numeric string on success, int on error).
- **URL handling**: `gatewayUrl` may or may not end with `/api`; `GoPay::buildUrl` appends it if missing, `buildEmbedUrl` strips it.
- **Config**: accepts either an array or a [src/Config.php](src/Config.php) object; `Config::parseUserConfig` normalizes to an array and applies defaults (`scope`, `language`, `timeout`). Language CZECH/SLOVAK maps to `Accept-Language: cs-CZ`, everything else `en-US`. Optional `customUserAgent` overrides the User-Agent (note: `Config::toArray()` does not include it, so it only works with array config).
- **Token cache key** is `clientId-gatewayUrl-scope` (`OAuth2::getClient`). Custom caches implement `Token\TokenCache`; custom loggers implement `Http\Log\Logger` (passed as `$services['cache']` / `$services['logger']`).
- **Enums** in [src/Definition/](src/Definition/) are plain classes with string constants mirroring GoPay code lists (payment instruments, swift codes, currencies, statuses, etc.).
- Legacy/deprecated: EET methods, `refundPayment` with an array (delegates to `refundPaymentEET`), and `paymentsSupercash()` in `factory.php` (references a `PaymentsSupercash` class that no longer exists).
