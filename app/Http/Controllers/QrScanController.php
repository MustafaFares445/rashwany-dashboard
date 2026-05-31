<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrScanController extends Controller
{
    public function __invoke(Request $request, AttendanceService $attendance, QrCodeService $qrCodes): JsonResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'qr_token' => ['required', 'string'],
            'scanned_at' => ['nullable', 'date'],
            'device_info' => ['nullable', 'string'],
            'location_id' => ['nullable', 'string'],
        ]);

        $member = Member::query()->findOrFail($data['member_id']);
        $qrCode = $qrCodes->findByToken($data['qr_token']);

        $payload = [
            'scanned_at' => $data['scanned_at'] ?? null,
            'device_info' => $data['device_info'] ?? $request->userAgent(),
            'location_id' => $data['location_id'] ?? null,
            'ip_address' => $request->ip(),
            'purpose' => $data['purpose'] ?? null,
        ];

        $outcome = $attendance->processScan($member, $qrCode, $payload);

        $statusCode = $outcome['result']->value === 'rejected' ? 422 : 200;

        return response()->json([
            'result' => $outcome['result']->value,
            'failure_reason' => $outcome['failure_reason'],
            'scan_id' => $outcome['scan']->id,
            'session_id' => $outcome['session']?->id,
        ], $statusCode);
    }
}
