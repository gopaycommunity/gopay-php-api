<?php

namespace GoPay;

interface Auth
{
    /** @return \GoPay\Token\AccessToken */
    public function authorize();
}
