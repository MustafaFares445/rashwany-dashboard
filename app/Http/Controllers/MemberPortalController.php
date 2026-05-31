<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\MemberPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberPortalController extends Controller
{
    public function profile(Request $request, MemberPortalService $portal): JsonResponse
    {
        $member = $this->resolveMember($request);

        return response()->json($portal->profile($member));
    }

    public function dashboard(Request $request, MemberPortalService $portal): JsonResponse
    {
        $member = $this->resolveMember($request);

        return response()->json($portal->dashboard($member));
    }

    public function subscription(Request $request, MemberPortalService $portal): JsonResponse
    {
        $member = $this->resolveMember($request);
        $subscription = $portal->subscription($member);

        return response()->json([
            'data' => $subscription,
        ]);
    }

    public function sessions(Request $request, MemberPortalService $portal): JsonResponse
    {
        $member = $this->resolveMember($request);
        $perPage = max(1, min(100, (int) $request->integer('per_page', 20)));

        return response()->json($portal->sessions($member, $perPage));
    }

    public function payments(Request $request, MemberPortalService $portal): JsonResponse
    {
        $member = $this->resolveMember($request);
        $perPage = max(1, min(100, (int) $request->integer('per_page', 20)));

        return response()->json($portal->payments($member, $perPage));
    }

    public function correctionRequests(Request $request, MemberPortalService $portal): JsonResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'session_id' => ['nullable', 'integer', 'exists:attendance_sessions,id'],
            'type' => ['required', 'string'],
            'requested_check_in_at' => ['nullable', 'date'],
            'requested_check_out_at' => ['nullable', 'date'],
            'message' => ['nullable', 'string'],
        ]);

        $member = Member::query()->findOrFail($data['member_id']);
        $payload = $portal->createCorrectionRequest($member, $data);

        return response()->json([
            'data' => $payload,
        ], 201);
    }

    private function resolveMember(Request $request): Member
    {
        $memberId = $request->integer('member_id');

        abort_unless($memberId, 422, 'member_id is required.');

        return Member::query()->findOrFail($memberId);
    }
}

