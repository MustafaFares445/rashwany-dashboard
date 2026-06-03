<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ResourcePageInsights;
use Carbon\CarbonInterface;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResourceBusinessStatsWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return ResourcePageInsights::current()['title'].' Overview';
    }

    protected function getStats(): array
    {
        $config = ResourcePageInsights::current();

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $config['model'];
        $dateColumn = $config['date_column'];

        $startOfThisMonth = now()->startOfMonth();
        $endOfThisMonth = now()->endOfMonth();
        $startOfLastMonth = now()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = now()->subMonthNoOverflow()->endOfMonth();

        $monthlyCounts = $this->monthlyCounts($modelClass, $dateColumn);

        $total = $modelClass::query()->count();
        $thisMonth = $modelClass::query()
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$startOfThisMonth, $endOfThisMonth])
            ->count();
        $lastMonth = $modelClass::query()
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$startOfLastMonth, $endOfLastMonth])
            ->count();

        $delta = $thisMonth - $lastMonth;
        $deltaPercent = $lastMonth > 0
            ? round(($delta / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100.0 : 0.0);

        $stats = [
            Stat::make('Total '.$config['title'], number_format($total))
                ->description('All time')
                ->chart($monthlyCounts)
                ->color('primary'),
            Stat::make($config['title'].' this month', number_format($thisMonth))
                ->description('From '.now()->startOfMonth()->format('M d'))
                ->chart($monthlyCounts)
                ->color('success'),
            Stat::make($config['title'].' last month', number_format($lastMonth))
                ->description(now()->subMonthNoOverflow()->format('F Y'))
                ->chart($monthlyCounts)
                ->color('gray'),
            Stat::make('MoM trend', ($delta >= 0 ? '+' : '').number_format($deltaPercent, 1).'%')
                ->description(($delta >= 0 ? '+' : '').number_format($delta).' vs last month')
                ->descriptionIcon($delta >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($monthlyCounts)
                ->color($delta >= 0 ? 'success' : 'danger'),
        ];

        $amountColumn = $config['amount_column'];

        if (is_string($amountColumn) && $amountColumn !== '') {
            $amountDateColumn = $config['amount_date_column'] ?: $dateColumn;
            $amountQuery = $modelClass::query()
                ->whereNotNull($amountDateColumn)
                ->whereBetween($amountDateColumn, [$startOfThisMonth, $endOfThisMonth]);

            $amountStatusColumn = $config['amount_status_column'];
            $amountStatuses = $config['amount_statuses'];

            if (is_string($amountStatusColumn) && is_array($amountStatuses) && $amountStatuses !== []) {
                $amountQuery->whereIn($amountStatusColumn, $amountStatuses);
            }

            $amountThisMonth = (float) $amountQuery->sum($amountColumn);

            $stats[] = Stat::make($config['amount_label'], number_format($amountThisMonth, 2).' USD')
                ->description(now()->format('F Y'))
                ->color('warning');
        }

        return $stats;
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     * @return array<int, int>
     */
    private function monthlyCounts(string $modelClass, string $dateColumn): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $diff): string => now()->subMonths($diff)->format('Y-m'));

        $raw = $modelClass::query()
            ->whereNotNull($dateColumn)
            ->where($dateColumn, '>=', now()->subMonths(5)->startOfMonth())
            ->get([$dateColumn])
            ->groupBy(fn ($record): ?string => $this->formatRecordMonth($record->{$dateColumn}))
            ->map(fn ($items): int => $items->count());

        return $months
            ->map(fn (string $month): int => (int) $raw->get($month, 0))
            ->all();
    }

    private function formatRecordMonth(mixed $value): ?string
    {
        if (! $value instanceof CarbonInterface) {
            return null;
        }

        return $value->format('Y-m');
    }
}
