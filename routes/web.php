<?php

use App\Http\Controllers\MemberCheckInController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/check-in', [MemberCheckInController::class, 'show'])->name('member.checkin');
Route::post('/check-in', [MemberCheckInController::class, 'process'])->name('member.checkin.process');

Route::middleware('auth')->prefix('admin/reports/export')->name('reports.export.')->group(function () {
    Route::get('sessions', [ReportExportController::class, 'sessions'])->name('sessions');
    Route::get('payments', [ReportExportController::class, 'payments'])->name('payments');
    Route::get('members', [ReportExportController::class, 'members'])->name('members');
    Route::get('subscriptions', [ReportExportController::class, 'subscriptions'])->name('subscriptions');
    Route::get('correction-requests', [ReportExportController::class, 'correctionRequests'])->name('correction-requests');
    Route::get('audit-logs', [ReportExportController::class, 'auditLogs'])->name('audit-logs');
});
