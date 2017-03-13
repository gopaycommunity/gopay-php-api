<?php

namespace GoPay;

class Eshop
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
     * @param string $currency
     * @return \GoPay\Http\Response
     */
    public function getPaymentInstruments($currency){
        return $this->api("/{$this->gopay->getConfig('goid')}/payment-instruments/" . $currency, GoPay::FORM);
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
                    "eshops/eshop{$urlPath}",
                    $contentType,
                    "Bearer {$token->token}",
                    $data
            );
        }
        return $token->response;
    }
}