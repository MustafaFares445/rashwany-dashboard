<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceSession;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlySessionsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Monthly Sessions (Last 6 Months)';

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $diff) => now()->subMonths($diff)->format('Y-m'))
            ->values();

        if (! $months->contains(now()->format('Y-m'))) {
            $months->push(now()->format('Y-m'));
        }

        $raw = AttendanceSession::query()
            ->where('check_in_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['check_in_at'])
            ->groupBy(fn (AttendanceSession $session) => $session->check_in_at?->format('Y-m'))
            ->map(fn ($items) => $items->count());

        return [
            'datasets' => [
                [
                    'label' => 'Sessions',
                    'data' => $months->map(fn (string $month) => $raw->get($month, 0))->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                ],
            ],
            'labels' => $months->map(fn (string $month) => Carbon::createFromFormat('Y-m', $month)->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
