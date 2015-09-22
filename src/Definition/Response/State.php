<?php

namespace GoPay\Definition\Response;

/**
 * recurrence_state - https://doc.gopay.com/en/?php#additional_params
 * preauthorization.state - https://doc.gopay.com/en/?php#pre-authorized-payment
 */
class State
{
    const REQUESTED = 'REQUESTED';
    const STARTED = 'STARTED';
    const STOPPED = 'STOPPED';
}
