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
                'status' => 200,
                'message' => 'Berhasil',
                'data' => $packageTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());

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
                'kategori_paket' => 'required|string|max:255',
                'nama_tipe_paket' => 'required|string|max:255',
            ],
            [
                'kategori_paket.required' => 'Kategori paket wajib diisi',
                'kategori_paket.string' => 'Kategori paket harus berupa teks',
                'kategori_paket.max' => 'Kategori paket maksimal 255 karakter',
                'nama_tipe_paket.required' => 'Nama tipe paket wajib diisi',
                'nama_tipe_paket.string' => 'Nama tipe paket harus berupa teks',
                'nama_tipe_paket.max' => 'Nama tipe paket maksimal 255 karakter',
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

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|integer|exists:package_types,id',
                'kategori_paket' => 'required|string|max:255',
                'nama_tipe_paket' => 'required|string|max:255',
            ],
            [
                'id.required' => 'ID tipe paket wajib diisi',
                'id.exists' => 'Tipe paket tidak ditemukan',
                'kategori_paket.required' => 'Kategori paket wajib diisi',
                'kategori_paket.string' => 'Kategori paket harus berupa teks',
                'kategori_paket.max' => 'Kategori paket maksimal 255 karakter',
                'nama_tipe_paket.required' => 'Nama tipe paket wajib diisi',
                'nama_tipe_paket.string' => 'Nama tipe paket harus berupa teks',
                'nama_tipe_paket.max' => 'Nama tipe paket maksimal 255 karakter',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Data tidak valid',
                'data' => $validator->errors()
            ], 422);
        }

        $packageType = PackageType::find($request->id);

        if (!$packageType) {
            return response()->json([
                'status' => 404,
                'message' => 'Tipe paket tidak ditemukan',
                'data' => null
            ], 404);
        }

        try {
            $packageType->update([
                'category' => $request->kategori_paket,
                'name' => $request->nama_tipe_paket,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Tipe paket updated successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating package type: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
}
