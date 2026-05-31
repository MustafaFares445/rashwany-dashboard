<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReportSnapshot extends Model
{
    protected $fillable = [
        'snapshot_date',
        'sessions_count',
        'open_sessions_count',
        'needs_review_sessions_count',
        'revenue_paid_total',
        'revenue_due_total',
        'active_members_count',
        'active_subscriptions_count',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'revenue_paid_total' => 'decimal:2',
        'revenue_due_total' => 'decimal:2',
    ];
}

