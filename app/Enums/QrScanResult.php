<?php

namespace App\Enums;

enum QrScanResult: string
{
    case Success = 'success';
    case Rejected = 'rejected';
    case NeedsReview = 'needs_review';
}
