<?php

namespace App\Filament\Resources\Concerns;

use App\Filament\Widgets\ResourceBusinessStatsWidget;
use App\Filament\Widgets\ResourceMonthlyTrendChartWidget;

trait HasBusinessInsightsWidgets
{
    protected function getHeaderWidgets(): array
    {
        return [
            ResourceBusinessStatsWidget::class,
            ResourceMonthlyTrendChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'md' => 1,
            'xl' => 2,
        ];
    }
}

