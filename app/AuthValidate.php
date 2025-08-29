<?php

namespace App;

use Illuminate\Http\Request;
use App\Models\Api\ApiClients;
use Illuminate\Http\JsonResponse;

class AuthValidate
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Validate API authentication
     * Returns error response if validation fails, null if success
     */
    public static function validateAuth(Request $request): ?JsonResponse
    {
        // Check token in header
        $token = $request->header('Token');
        if (!$token) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid atau tidak ada',
                'data' => null
            ], 401);
        }

        // Validate basic auth
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

        // TODO: Add token validation logic here if needed

        return null;
    }

    /**
     * Success response
     */
    public static function successResponse(string $message = 'Berhasil', $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Error response
     */
    public static function errorResponse(string $message, int $status = 400, $data = null): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Validation error response
     */
    public static function validationErrorResponse($errors, string $message = 'Data tidak valid'): JsonResponse
    {
        return response()->json([
            'status' => 422,
            'message' => $message,
            'data' => $errors
        ], 422);
    }

    /**
     * Server error response (500)
     */
    public static function serverErrorResponse(string $message = 'Terjadi kesalahan server', $data = null): JsonResponse
    {
        return response()->json([
            'status' => 500,
            'message' => $message,
            'data' => $data
        ], 500);
    }
}