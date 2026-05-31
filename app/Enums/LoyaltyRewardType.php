<?php

namespace App\Enums;

enum LoyaltyRewardType: string
{
    case FreeHours = 'free_hours';
    case Gift = 'gift';
    case Note = 'note';
    case Badge = 'badge';
    case Manual = 'manual';
}

