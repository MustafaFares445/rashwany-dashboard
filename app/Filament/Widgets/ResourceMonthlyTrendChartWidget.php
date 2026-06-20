<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ResourcePageInsights;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;

class ResourceMonthlyTrendChartWidget extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '240px';

    public function getHeading(): ?string
    {
        return ResourcePageInsights::current()['title'].' Monthly Trend (Last 6 Months)';
    }

    protected function getData(): array
    {
        $config = ResourcePageInsights::current();

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $config['model'];
        $dateColumn = $config['date_column'];

        $months = collect(range(5, 0))
            ->map(fn (int $diff): string => now()->subMonths($diff)->format('Y-m'));

        $raw = $modelClass::query()
            ->whereNotNull($dateColumn)
            ->where($dateColumn, '>=', now()->subMonths(5)->startOfMonth())
            ->get([$dateColumn])
            ->groupBy(fn ($record): ?string => $this->formatRecordMonth($record->{$dateColumn}))
            ->map(fn ($items): int => $items->count());

        return [
            'datasets' => [
                [
                    'label' => $config['title'],
                    'data' => $months->map(fn (string $month): int => (int) $raw->get($month, 0))->all(),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->map(fn (string $month): string => Carbon::createFromFormat('Y-m', $month)->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return ResourcePageInsights::current()['chart_type'] ?? 'line';
    }

    private function formatRecordMonth(mixed $value): ?string
    {
        if (! $value instanceof CarbonInterface) {
            return null;
        }

        return $value->format('Y-m');
    }
}
