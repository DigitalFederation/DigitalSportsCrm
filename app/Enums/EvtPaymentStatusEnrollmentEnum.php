<?php

namespace App\Enums;

enum EvtPaymentStatusEnrollmentEnum: string
{
    case pending = 'Pending';
    case active = 'Active';
    case cancelled = 'Cancelled';
}
