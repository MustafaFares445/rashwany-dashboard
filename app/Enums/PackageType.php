<?php

namespace App\Enums;

enum PackageType: string
{
    case Hourly = 'hourly';
    case HoursWeekly = 'hours_weekly';
    case HoursMonthly = 'hours_monthly';
    case UnlimitedWeekly = 'unlimited_weekly';
    case UnlimitedMonthly = 'unlimited_monthly';
}
