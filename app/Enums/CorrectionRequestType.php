<?php

namespace App\Enums;

enum CorrectionRequestType: string
{
    case ForgotCheckOut = 'forgot_check_out';
    case WrongCheckIn = 'wrong_check_in';
    case WrongCheckOut = 'wrong_check_out';
    case PaymentIssue = 'payment_issue';
    case Other = 'other';
}
