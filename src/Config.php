<?php

namespace GoPay;

use GoPay\Definition\Language;
use GoPay\Definition\TokenScope;

class Config
{
    /**
     * @param string|null $clientId {@see https://doc.gopay.com/#access-token}
     * @param string|null $clientSecret {@see https://doc.gopay.com/#access-token}
     * @param string|null $goid Default GoPay account used in `createPayment` if `target` is not specified.
     * @param string|null $gatewayUrl {@see https://help.gopay.com/en/knowledge-base/integration-of-payment-gateway/integration-of-payment-gateway-1/how-do-i-integrate-the-payment-gateway}
     */
    public function __construct(
        public ?string $clientId = null,
        public ?string $clientSecret = null,
        public ?string $goid = null,
        public ?string $gatewayUrl = null,
        public ?string $customUserAgent = null,
    ) {
    }

    /**
     * {@see https://doc.gopay.com/#access-token}
     *
     * @var string
     */
    public string $scope = TokenScope::ALL;

    /**
     * Language used in `createPayment` if `lang` is not specified + used for
     * {@see https://doc.gopay.com/#errors localization of errors}.
     *
     * @var string
     */
    public string $language = Language::ENGLISH;

    /**
     * Browser timeout in seconds.
     *
     * @var int
     */
    public int $timeout = 30;

    /**
     * @param array|Config $userConfig
     * @param bool $defaultArrayValues
     * @return array
     */
    public static function parseUserConfig(array|Config $userConfig, bool $defaultArrayValues = true): array
    {
        if (is_array($userConfig)) {
            if ($defaultArrayValues) {
                return $userConfig + [
                        'scope' => TokenScope::ALL,
                        'language' => Language::ENGLISH,
                        'timeout' => 30
                    ];
            }

            return $userConfig;
        } else {
            return $userConfig->toArray();
        }
    }

    /**
     * @param array $arr
     * @return Config
     */
    public static function fromArray(array $arr): Config
    {
        $config = new Config();

        $config->goid = $arr['goid'] ?? null;
        $config->clientId = $arr['clientId'] ?? null;
        $config->clientSecret = $arr['clientSecret'] ?? null;
        $config->gatewayUrl = $arr['gatewayUrl'] ?? null;

        // Properties with default values are set only if their value is provided
        if (isset($arr['scope'])) {
            $config->scope = $arr['scope'];
        }
        if (isset($arr['language'])) {
            $config->language = $arr['language'];
        }
        if (isset($arr['timeout'])) {
            $config->timeout = $arr['timeout'];
        }

        return $config;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'goid' => $this->goid,
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'gatewayUrl' => $this->gatewayUrl,
            'scope' => $this->scope,
            'language' => $this->language,
            'timeout' => $this->timeout,
        ];
    }
}