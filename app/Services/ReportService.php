<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\CorrectionRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    public function sessionsQuery(array $filters = []): Builder
    {
        $query = AttendanceSession::query()->with(['member', 'subscription.package']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->where('check_in_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('check_in_at', '<=', $filters['to']);
        }

        return $query;
    }

    public function paymentsQuery(array $filters = []): Builder
    {
        $query = Payment::query()->with(['member', 'subscription']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->where('paid_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('paid_at', '<=', $filters['to']);
        }

        return $query;
    }

    public function membersQuery(array $filters = []): Builder
    {
        $query = Member::query()->with(['activeSubscription.package']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query;
    }

    public function subscriptionsQuery(array $filters = []): Builder
    {
        $query = Subscription::query()->with(['member', 'package']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->where('starts_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('starts_at', '<=', $filters['to']);
        }

        return $query;
    }

    public function correctionRequestsQuery(array $filters = []): Builder
    {
        $query = CorrectionRequest::query()->with(['member', 'session']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query;
    }

    public function auditLogsQuery(array $filters = []): Builder
    {
        $query = AuditLog::query();

        if (! empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query;
    }
}
