<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Paid = 'paid';
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';
}
