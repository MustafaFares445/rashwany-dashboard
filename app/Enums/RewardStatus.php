<?php

namespace App\Enums;

enum RewardStatus: string
{
    case Pending = 'pending';
    case Granted = 'granted';
    case Cancelled = 'cancelled';
}

