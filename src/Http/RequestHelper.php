<?php

namespace GoPay\Http;

class RequestHelper
{
    public function getBaseApiUrl($isProductionMode)
    {
        static $urls = [
            true => 'https://gate.gopay.cz/api/',
            false => 'https://gw.sandbox.gopay.com/api/'
        ];
        return $urls[(bool) $isProductionMode];
    }

    public function encodeData(array $headers, array $data)
    {
        if ($data) {
            $encoder = $headers['Content-Type'] == Browser::FORM ? 'http_build_query' : 'json_encode';
            return $encoder($data);
        }
        return '';
    }

    public function normalizeHeaders(array $headers)
    {
        if (is_array($headers['Authorization'])) {
            $credentials = implode(':', $headers['Authorization']);
            $headers['Authorization'] = 'Basic ' . base64_encode($credentials);
        }
        return $headers;
    }
}
