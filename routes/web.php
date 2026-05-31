<?php

use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->prefix('admin/reports/export')->name('reports.export.')->group(function () {
    Route::get('sessions', [ReportExportController::class, 'sessions'])->name('sessions');
    Route::get('payments', [ReportExportController::class, 'payments'])->name('payments');
    Route::get('members', [ReportExportController::class, 'members'])->name('members');
    Route::get('subscriptions', [ReportExportController::class, 'subscriptions'])->name('subscriptions');
    Route::get('correction-requests', [ReportExportController::class, 'correctionRequests'])->name('correction-requests');
    Route::get('audit-logs', [ReportExportController::class, 'auditLogs'])->name('audit-logs');
});
