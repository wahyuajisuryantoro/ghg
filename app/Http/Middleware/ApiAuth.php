<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Api\ApiTokens;
use App\Models\Api\ApiClients;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Token');
        if (!$token) {
            $response = response()->json([
                'status' => 401,
                'message' => 'Token tidak valid atau tidak ada',
                'data' => null
            ], 401);
        } else {
            $clientId = $request->getUser();
            $passwordBasicAuth = $request->getPassword();

            if (!$clientId || !$passwordBasicAuth) {
                $response = response()->json([
                    'status' => 401,
                    'message' => 'Basic Auth diperlukan',
                    'data' => null
                ], 401);
            } else {
                $client = ApiClients::where('client_id', $clientId)
                    ->where('password_basic_auth', $passwordBasicAuth)
                    ->where('is_active', true)
                    ->first();

                if (!$client) {
                    $response = response()->json([
                        'status' => 401,
                        'message' => 'Basic Auth tidak valid',
                        'data' => null
                    ], 401);
                } else {
                    $tokenRecord = ApiTokens::where('token', $token)
                        ->where('expired', '>=', now())
                        ->first();

                    if (!$tokenRecord) {
                        $response = response()->json([
                            'status' => 401,
                            'message' => 'Token sudah expired atau tidak valid',
                            'data' => null
                        ], 401);
                    } else {
                        $response = $next($request);
                    }
                }
            }
        }

        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, Token');

        return $response;
    }
}