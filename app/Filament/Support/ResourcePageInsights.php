<?php

namespace App\Filament\Support;

use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\CorrectionRequest;
use App\Models\LoyaltyRule;
use App\Models\Member;
use App\Models\Package;
use App\Models\Payment;
use App\Models\QrCode;
use App\Models\QrScan;
use App\Models\Reward;
use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Support\Str;

class ResourcePageInsights
{
    public static function current(): array
    {
        $routeName = request()->route()?->getName();
        $slug = Str::of((string) $routeName)
            ->after('.resources.')
            ->before('.')
            ->value();

        return self::forSlug($slug);
    }

    public static function forSlug(string $slug): array
    {
        return match ($slug) {
            'members' => self::build('Members', Member::class, 'created_at', chartType: 'line'),
            'attendance-sessions' => self::build('Attendance Sessions', AttendanceSession::class, 'check_in_at', chartType: 'line'),
            'subscriptions' => self::build('Subscriptions', Subscription::class, 'starts_at', chartType: 'line'),
            'payments' => self::build(
                'Payments',
                Payment::class,
                'created_at',
                chartType: 'bar',
                amountColumn: 'amount',
                amountDateColumn: 'paid_at',
                amountStatusColumn: 'status',
                amountStatuses: ['paid', 'partial'],
                amountLabel: 'Collected this month'
            ),
            'qr-codes' => self::build('QR Codes', QrCode::class, 'created_at', chartType: 'line'),
            'qr-scans' => self::build('QR Scans', QrScan::class, 'scanned_at', chartType: 'line'),
            'correction-requests' => self::build('Correction Requests', CorrectionRequest::class, 'created_at', chartType: 'line'),
            'rewards' => self::build('Rewards', Reward::class, 'created_at', chartType: 'line'),
            'packages' => self::build('Packages', Package::class, 'created_at', chartType: 'bar'),
            'loyalty-rules' => self::build('Loyalty Rules', LoyaltyRule::class, 'created_at', chartType: 'line'),
            'settings' => self::build('Settings', Setting::class, 'created_at', chartType: 'line'),
            'audit-logs' => self::build('Audit Logs', AuditLog::class, 'created_at', chartType: 'bar'),
            default => self::build('Records', AuditLog::class, 'created_at', chartType: 'line'),
        };
    }

    private static function build(
        string $title,
        string $model,
        string $dateColumn,
        string $chartType = 'line',
        ?string $amountColumn = null,
        ?string $amountDateColumn = null,
        ?string $amountStatusColumn = null,
        array $amountStatuses = [],
        string $amountLabel = 'Amount this month'
    ): array {
        return [
            'title' => $title,
            'model' => $model,
            'date_column' => $dateColumn,
            'chart_type' => $chartType,
            'amount_column' => $amountColumn,
            'amount_date_column' => $amountDateColumn,
            'amount_status_column' => $amountStatusColumn,
            'amount_statuses' => $amountStatuses,
            'amount_label' => $amountLabel,
        ];
    }
}

