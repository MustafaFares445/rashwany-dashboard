<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Monthly Paid Revenue (Last 6 Months)';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '240px';

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $diff) => now()->subMonths($diff)->format('Y-m'))
            ->values();

        if (! $months->contains(now()->format('Y-m'))) {
            $months->push(now()->format('Y-m'));
        }

        $raw = Payment::query()
            ->whereIn('status', ['paid', 'partial'])
            ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['paid_at', 'amount'])
            ->groupBy(fn (Payment $payment) => $payment->paid_at?->format('Y-m'))
            ->map(fn ($items) => round((float) $items->sum('amount'), 2));

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $months->map(fn (string $month) => $raw->get($month, 0))->all(),
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $months->map(fn (string $month) => Carbon::createFromFormat('Y-m', $month)->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
