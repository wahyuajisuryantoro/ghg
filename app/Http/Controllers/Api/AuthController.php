<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Api\ApiTokens;
use App\Models\Api\ApiClients;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function generateToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string',
            'client_key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Data tidak valid',
                'data' => $validator->errors()
            ], 422);
        }

        $client = ApiClients::where('client_id', $request->client_id)
            ->where('client_key', $request->client_key)
            ->where('is_active', true)
            ->first();

        if (!$client) {
            return response()->json([
                'status' => 401,
                'message' => 'Client ID atau Client Key tidak valid',
                'data' => null
            ], 401);
        }

        $expiredAt = now()->addMinutes(240);
        $token = md5($client->client_id . $client->client_key . $expiredAt->timestamp);

        ApiTokens::create([
            'token' => $token,
            'expired' => $expiredAt,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Generate Token Berhasil',
            'data' => [
                'token' => $token,
                'expired' => $expiredAt->format('Y-m-d H:i:s')
            ]
        ], 200);
    }
}
