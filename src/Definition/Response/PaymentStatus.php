<?php

namespace GoPay\Definition\Response;

class PaymentStatus
{

    const CREATED = 'CREATED';
    const PAYMENT_METHOD_CHOSEN = 'PAYMENT_METHOD_CHOSEN';
    const PAID = 'PAID';
    const AUTHORIZED = 'AUTHORIZED';
    const CANCELED = 'CANCELED';
    const TIMEOUTED = 'TIMEOUTED';
    const REFUNDED = 'REFUNDED';
    const PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';
}
