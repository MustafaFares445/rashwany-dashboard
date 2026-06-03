<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberCheckInController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    public function show(): View
    {
        return view('member.check-in');
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'pin' => 'required|string',
        ]);

        $result = $this->attendanceService->processPhoneAndPin($validated['phone'], $validated['pin']);

        return response()->json($result);
    }
}
