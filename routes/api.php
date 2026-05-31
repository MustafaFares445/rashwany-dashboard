<?php

use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\QrScanController;
use Illuminate\Support\Facades\Route;

Route::post('qr-scan', QrScanController::class);
Route::post('qr/scan', QrScanController::class);

Route::prefix('member')->group(function () {
    Route::get('profile', [MemberPortalController::class, 'profile']);
    Route::get('dashboard', [MemberPortalController::class, 'dashboard']);
    Route::get('subscription', [MemberPortalController::class, 'subscription']);
    Route::get('sessions', [MemberPortalController::class, 'sessions']);
    Route::get('payments', [MemberPortalController::class, 'payments']);
    Route::post('correction-requests', [MemberPortalController::class, 'correctionRequests']);
});
