<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use UnitEnum;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reports';
    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected string $view = 'filament.pages.reports';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportSessions')
                ->label('Export Sessions (Excel CSV)')
                ->url(fn (): string => route('reports.export.sessions'), shouldOpenInNewTab: true),
            Action::make('exportPayments')
                ->label('Export Payments (Excel CSV)')
                ->url(fn (): string => route('reports.export.payments'), shouldOpenInNewTab: true),
            Action::make('exportMembers')
                ->label('Export Members (Excel CSV)')
                ->url(fn (): string => route('reports.export.members'), shouldOpenInNewTab: true),
            Action::make('exportSubscriptions')
                ->label('Export Subscriptions (Excel CSV)')
                ->url(fn (): string => route('reports.export.subscriptions'), shouldOpenInNewTab: true),
            Action::make('exportCorrectionRequests')
                ->label('Export Corrections (Excel CSV)')
                ->url(fn (): string => route('reports.export.correction-requests'), shouldOpenInNewTab: true),
            Action::make('exportAuditLogs')
                ->label('Export Audit Logs (Excel CSV)')
                ->url(fn (): string => route('reports.export.audit-logs'), shouldOpenInNewTab: true),
        ];
    }
}
