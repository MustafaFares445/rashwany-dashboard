<?php

namespace App\Enums;

enum LoyaltyTriggerType: string
{
    case TotalHours = 'total_hours';
    case SubscriptionMonths = 'subscription_months';
    case VisitCount = 'visit_count';
    case Birthday = 'birthday';
    case Manual = 'manual';
}

