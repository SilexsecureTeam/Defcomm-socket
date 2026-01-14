<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\QrLoginRequest;
use App\Http\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class QrLoginController extends Controller
{
    // 1) Desktop: create QR request (anonymous)
    public function create(Request $request)
    {
        // Cleanup old
        QrLoginRequest::where('expires_at', '<', now())->where('status', 'pending')->update(['status' => 'expired']);

        $qr = QrLoginRequest::create([
            'code' => (string) Str::uuid(),
            'expires_at' => now()->addMinutes(2),
        ]);

        return response()->json([
            'code' => $qr->code,
            'expires_at' => $qr->expires_at->toISOString(),
            // Optional: a compact string you can embed
            'qr_payload' => json_encode(['type' => 'qr-login', 'code' => $qr->code]),
        ], 201);
    }

    // 2) Desktop: poll QR status
    public function status($code)
    {
        $qr = QrLoginRequest::where('code', $code)->first();
        if (!$qr) return response()->json(['message' => 'Not found'], 404);

        // auto-expire
        if ($qr->status === 'pending' && $qr->expires_at->isPast()) {
            $qr->status = 'expired';
            $qr->save();
        }

        return response()->json([
            'status' => $qr->status,
            'approved_user_id' => $qr->approved_user_id,
        ]);
    }

    // 3) Mobile: approve the QR (requires mobile auth)
    public function approve(Request $request, $code)
    {
        $request->validate(['confirm' => 'required|boolean']);
        if (!$request->confirm) {
            return response()->json(['message' => 'Approval not confirmed'], 422);
        }

        $qr = QrLoginRequest::where('code', $code)->first();
        if (!$qr) return response()->json(['message' => 'Not found'], 404);

        if ($qr->expires_at->isPast() || $qr->status !== 'pending') {
            return response()->json(['message' => 'QR not pending or expired'], 422);
        }

        $qr->status = 'approved';
        $qr->approved_user_id = $request->user()->id;
        $qr->approved_at = now();
        $qr->save();

        return response()->json(['message' => 'Approved']);
    }

    // 4) Desktop: exchange approved QR for token (single-use)
    public function exchange(Request $request, $code)
    {
        $qr = QrLoginRequest::where('code', $code)->first();
        if (!$qr) return response()->json(['message' => 'Not found'], 404);

        if ($qr->status !== 'approved' || !$qr->approved_user_id) {
            return response()->json(['message' => 'Not approved'], 422);
        }
        if ($qr->redeemed_at) {
            return response()->json(['message' => 'Already redeemed'], 422);
        }

        $user = $qr->user;
        $token = $user->createToken('browser')->plainTextToken;

        $qr->status = 'redeemed';
        $qr->redeemed_at = now();
        $qr->save();

        $logDevice = (new AuthService())->authLogin($user, $request);
        if ($logDevice == 'block') {
            Auth::logout();
            return response()->json(['status' => 400, 'error' => "This device does not have access to this account"], 401);
        }
        return response()->json(
            [
                'status' => 200,
                'message' => 'Login successfully',
                'data' => $logDevice
            ],
            201
        );
    }
}
