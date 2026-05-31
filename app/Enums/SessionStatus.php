<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case NeedsReview = 'needs_review';
    case Corrected = 'corrected';
    case Cancelled = 'cancelled';
}
