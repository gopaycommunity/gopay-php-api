<?php

namespace GoPay\Definition\Response;

class PreAuthState
{
    public const REQUESTED = 'REQUESTED';
    public const AUTHORIZED = 'AUTHORIZED';
    public const CAPTURED = 'CAPTURED';
    public const CANCELED = 'CANCELED';
}
