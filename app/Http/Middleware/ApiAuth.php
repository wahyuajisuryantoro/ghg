<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Api\ApiTokens;
use App\Models\Api\ApiClients;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Ambil token dari header
        $token = $request->header('Token');
        if (!$token) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid atau tidak ada',
                'data' => null
            ], 401);
        }

        $clientId = $request->getUser();
        $passwordBasicAuth = $request->getPassword();

        if (!$clientId || !$passwordBasicAuth) {
            return response()->json([
                'status' => 401,
                'message' => 'Basic Auth diperlukan',
                'data' => null
            ], 401);
        }

        $client = ApiClients::where('client_id', $clientId)
            ->where('password_basic_auth', $passwordBasicAuth)
            ->where('is_active', true)
            ->first();

        if (!$client) {
            return response()->json([
                'status' => 401,
                'message' => 'Basic Auth tidak valid',
                'data' => null
            ], 401);
        }

        $tokenRecord = ApiTokens::where('token', $token)
            ->where('expired', '>=', now())
            ->first();

        if (!$tokenRecord) {
            return response()->json([
                'status' => 401,
                'message' => 'Token sudah expired atau tidak valid',
                'data' => null
            ], 401);
        }

        return $next($request);
    }
}
