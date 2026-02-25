<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiClientProtection
{
    public function handle(Request $request, \Closure $next): Response
    {
        if ($this->hasValidServerIp($request) || $this->hasValidMobileSignature($request)) {
            return $next($request);
        }

        return response()->json([
            'status' => 401,
            'message' => 'Unauthorized API client',
        ], 401);
    }

    // ── Web / backend server: IP whitelist ───────────────────────────────────

    private function hasValidServerIp(Request $request): bool
    {
        $allowedIps = config('api_protection.allowed_ips', []);

        if (empty($allowedIps)) {
            return false;
        }

        return in_array($request->ip(), $allowedIps, strict: true);
    }

    // ── Mobile app: HMAC signature ───────────────────────────────────────────

    private function hasValidMobileSignature(Request $request): bool
    {
        $signature = $request->header('X-Api-Signature');
        $timestamp = $request->header('X-Api-Timestamp');
        $secret = config('api_protection.mobile_secret');

        if (!$signature || !$timestamp || !$secret) {
            return false;
        }

        // Replay attack protection
        $replayTtl = (int) config('api_protection.replay_ttl', 300);
        if (abs(time() - (int) $timestamp) > $replayTtl) {
            return hash_equals($expected, $signature);
        }
    }
}
