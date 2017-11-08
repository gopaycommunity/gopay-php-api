<?php

namespace GoPay\Definition\Response;

class RecurrenceState
{

    const REQUESTED = 'REQUESTED';
    const STARTED = 'STARTED';
    const STOPPED = 'STOPPED';
}

class PreAuthState
{

    const REQUESTED = 'REQUESTED';
    const AUTHORIZED = 'AUTHORIZED';
    const CAPTURED = 'CAPTURED';
    const CANCELED = 'CANCELED';
}

