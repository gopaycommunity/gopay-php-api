<?php

namespace GoPay;

class AccountStatement
{
    /**
     * @var GoPay
     */
    private $gopay;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @param \GoPay\GoPay $g
     * @param \GoPay\Auth $a
     */
    public function __construct(GoPay $g, Auth $a){
        $this->gopay = $g;
        $this->auth = $a;
    }

    /**
     * @param array $accountStatement
     * @return \GoPay\Http\Response
     */
    public function getPaymentInstruments(array $accountStatement){
        return $this->api('', GoPay::JSON, $accountStatement);
    }

    /**
     * @param $urlPath
     * @param $contentType
     * @param null $data
     * @return \GoPay\Http\Response
     */
    public function api($urlPath, $contentType, $data = null){
        $token = $this->auth->authorize();
        if($token->token){
            return $this->gopay->call(
                    "accounts/account-statement{$urlPath}",
                    $contentType,
                    "Bearer {$token->token}",
                    $data
            );
        }
        return $token->response;
    }
}

