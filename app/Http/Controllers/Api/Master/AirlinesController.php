<?php

namespace App\Http\Controllers\Api\Master;

use App\AuthValidate;
use Illuminate\Http\Request;
use App\Models\Api\ApiClients;
use Illuminate\Http\JsonResponse;
use App\Models\Api\Master\Airlines;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AirlinesController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $airlines = Airlines::all(['idairlines', 'airlinesname']);

            return response()->json([
                'status' => 200,
                'message' => 'Daftar Airlines berhasil diambil',
                'data' => $airlines
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching airlines: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'airlinesname' => 'required|string|max:255|unique:airlines,airlinesname'
            ],
            [
                'airlinesname.unique' => 'Nama Airlines sudah terpakai',
                'airlinesname.required' => 'Nama Airlines wajib diisi'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Data tidak valid',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $airlines = Airlines::create([
                'airlinesname' => $request->airlinesname
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Airlines Berhasil Dibuat',
                'data' => [
                    'airlines' => [
                        'idairlines' => $airlines->idairlines,
                        'airlinesname' => $airlines->airlinesname
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating airlines: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }


    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'idairlines' => 'required|integer|exists:airlines,idairlines',
                'airlinesname' => 'required|string|max:255|unique:airlines,airlinesname,' . $request->idairlines . ',idairlines'
            ],
            [
                'airlinesname.unique' => 'Nama Airlines sudah terpakai',
                'airlinesname.required' => 'Nama Airlines wajib diisi'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Data tidak valid',
                'data' => $validator->errors()
            ], 422);
        }

        $airlines = Airlines::find($request->idairlines);

        if (!$airlines) {
            return response()->json([
                'status' => 404,
                'message' => 'Airlines tidak ditemukan',
                'data' => null
            ], 404);
        }

        try {
            $airlines->update([
                'airlinesname' => $request->airlinesname
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Airlines updated successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating airlines: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

}
