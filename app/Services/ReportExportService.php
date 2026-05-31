<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function exportSessionsCsv(array $filters = []): StreamedResponse
    {
        $query = $this->reports->sessionsQuery($filters)->orderBy('check_in_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Session ID',
                'Member',
                'Package',
                'Status',
                'Check In',
                'Check Out',
                'Raw Minutes',
                'Billable Minutes',
            ]);

            $query->chunk(200, function ($sessions) use ($handle) {
                foreach ($sessions as $session) {
                    fputcsv($handle, [
                        $session->id,
                        $session->member?->name,
                        $session->subscription?->package?->name,
                        $session->status?->value ?? $session->status,
                        optional($session->check_in_at)->toDateTimeString(),
                        optional($session->check_out_at)->toDateTimeString(),
                        $session->raw_duration_minutes,
                        $session->billable_duration_minutes,
                    ]);
                }
            });

            fclose($handle);
        }, 'sessions-report.csv');
    }

    public function exportPaymentsCsv(array $filters = []): StreamedResponse
    {
        $query = $this->reports->paymentsQuery($filters)->orderBy('paid_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Payment ID',
                'Member',
                'Subscription',
                'Amount',
                'Currency',
                'Method',
                'Status',
                'Paid At',
                'Due At',
            ]);

            $query->chunk(200, function ($payments) use ($handle) {
                foreach ($payments as $payment) {
                    fputcsv($handle, [
                        $payment->id,
                        $payment->member?->name,
                        $payment->subscription?->id,
                        $payment->amount,
                        $payment->currency,
                        $payment->payment_method?->value ?? $payment->payment_method,
                        $payment->status?->value ?? $payment->status,
                        optional($payment->paid_at)->toDateTimeString(),
                        optional($payment->due_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'payments-report.csv');
    }

    public function exportMembersCsv(array $filters = []): StreamedResponse
    {
        $query = $this->reports->membersQuery($filters)->orderBy('created_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Member ID',
                'Name',
                'Phone',
                'Email',
                'Status',
                'Current Package',
                'Current Due Amount',
                'Created At',
            ]);

            $query->chunk(200, function ($members) use ($handle) {
                foreach ($members as $member) {
                    fputcsv($handle, [
                        $member->id,
                        $member->name,
                        $member->phone,
                        $member->email,
                        $member->status?->value ?? $member->status,
                        $member->activeSubscription?->package?->name,
                        $member->activeSubscription?->due_amount,
                        optional($member->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'members-report.csv');
    }

    public function exportSubscriptionsCsv(array $filters = []): StreamedResponse
    {
        $query = $this->reports->subscriptionsQuery($filters)->orderBy('starts_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Subscription ID',
                'Member',
                'Package',
                'Status',
                'Starts At',
                'Ends At',
                'Total Hours',
                'Remaining Hours',
                'Used Hours',
                'Price',
                'Paid Amount',
                'Due Amount',
            ]);

            $query->chunk(200, function ($subscriptions) use ($handle) {
                foreach ($subscriptions as $subscription) {
                    fputcsv($handle, [
                        $subscription->id,
                        $subscription->member?->name,
                        $subscription->package?->name,
                        $subscription->status?->value ?? $subscription->status,
                        optional($subscription->starts_at)->toDateTimeString(),
                        optional($subscription->ends_at)->toDateTimeString(),
                        $subscription->total_hours,
                        $subscription->remaining_hours,
                        $subscription->used_hours,
                        $subscription->price,
                        $subscription->paid_amount,
                        $subscription->due_amount,
                    ]);
                }
            });

            fclose($handle);
        }, 'subscriptions-report.csv');
    }

    public function exportCorrectionRequestsCsv(array $filters = []): StreamedResponse
    {
        $query = $this->reports->correctionRequestsQuery($filters)->orderBy('created_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Request ID',
                'Member',
                'Session ID',
                'Type',
                'Status',
                'Requested Check In',
                'Requested Check Out',
                'Reviewed At',
                'Created At',
            ]);

            $query->chunk(200, function ($requests) use ($handle) {
                foreach ($requests as $request) {
                    fputcsv($handle, [
                        $request->id,
                        $request->member?->name,
                        $request->session_id,
                        $request->type?->value ?? $request->type,
                        $request->status?->value ?? $request->status,
                        optional($request->requested_check_in_at)->toDateTimeString(),
                        optional($request->requested_check_out_at)->toDateTimeString(),
                        optional($request->reviewed_at)->toDateTimeString(),
                        optional($request->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'correction-requests-report.csv');
    }

    public function exportAuditLogsCsv(array $filters = []): StreamedResponse
    {
        $query = $this->reports->auditLogsQuery($filters)->orderByDesc('created_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Log ID',
                'Action',
                'Entity Type',
                'Entity ID',
                'Actor Type',
                'Actor ID',
                'Reason',
                'IP Address',
                'Created At',
            ]);

            $query->chunk(200, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->action,
                        $log->entity_type,
                        $log->entity_id,
                        $log->actor_type?->value ?? $log->actor_type,
                        $log->actor_id,
                        $log->reason,
                        $log->ip_address,
                        optional($log->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'audit-logs-report.csv');
    }
}
