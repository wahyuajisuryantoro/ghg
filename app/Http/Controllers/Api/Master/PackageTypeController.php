<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Api\Master\PackageType;
use Illuminate\Support\Facades\Validator;

class PackageTypeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $packageTypes = PackageType::all();

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil',
                'data'    => $packageTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error( $e->getMessage());

            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan server',
                'data'    => null
            ], 500);
        }
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kategori_paket' => 'required|in:Umroh,Haji',
                'nama_tipe_paket' => 'required|string|max:255',
            ],
            [
                'kategori_paket.required' => 'Kategori paket wajib diisi',
                'kategori_paket.in' => 'Kategori paket harus Umroh atau Haji',
                'nama_tipe_paket.required' => 'Nama tipe paket wajib diisi',
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
            $packageType = PackageType::create([
                'category' => $request->kategori_paket,
                'name' => $request->nama_tipe_paket,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Tipe Paket Berhasil Dibuat',
                'data' => [
                    'tipe_paket_id' => $packageType->id
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error creating package type: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
}
