<?php

namespace App\Http\Controllers;

use App\Services\ReportExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function sessions(ReportExportService $exports): StreamedResponse
    {
        return $exports->exportSessionsCsv();
    }

    public function payments(ReportExportService $exports): StreamedResponse
    {
        return $exports->exportPaymentsCsv();
    }

    public function members(ReportExportService $exports): StreamedResponse
    {
        return $exports->exportMembersCsv();
    }

    public function subscriptions(ReportExportService $exports): StreamedResponse
    {
        return $exports->exportSubscriptionsCsv();
    }

    public function correctionRequests(ReportExportService $exports): StreamedResponse
    {
        return $exports->exportCorrectionRequestsCsv();
    }

    public function auditLogs(ReportExportService $exports): StreamedResponse
    {
        return $exports->exportAuditLogsCsv();
    }
}

