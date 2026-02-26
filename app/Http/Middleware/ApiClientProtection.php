<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiClientProtection
{
    public function handle(Request $request, \Closure $next): Response
    {
        if ($this->hasValidMobileSignature($request) || $this->hasValidServerIp($request)) {
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

        $clientIp = $request->ip();
        $serverIp = $_SERVER['SERVER_ADDR'] ?? null;

        Log::info('API request — client IP: '.$clientIp.' | server IP: '.$serverIp);

        return in_array($clientIp, $allowedIps, strict: true)
            || ($serverIp !== null && in_array($serverIp, $allowedIps, strict: true));
    }

    // ── Mobile app: HMAC signature ───────────────────────────────────────────
    private function hasValidMobileSignature(Request $request): bool
    {
        $signature = $request->header('X-Api-Signature');
        $secret = config('api_protection.mobile_secret');

        if (!$signature || !$secret) {
            Log::warning('Missing required API signature components', [
                'has_signature' => !empty($signature),
                'has_secret' => !empty($secret),
            ]);

            return false;
        }

        return hash_equals($secret, $signature);
    }
}
