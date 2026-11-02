<?php

namespace GoPay\Definition\Response;

class PaymentStatus
{
    public const CREATED = 'CREATED';
    public const PAYMENT_METHOD_CHOSEN = 'PAYMENT_METHOD_CHOSEN';
    public const PAID = 'PAID';
    public const AUTHORIZED = 'AUTHORIZED';
    public const CANCELED = 'CANCELED';
    public const TIMEOUTED = 'TIMEOUTED';
    public const REFUNDED = 'REFUNDED';
    public const PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';
}
