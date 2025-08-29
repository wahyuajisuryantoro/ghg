<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Api\Master\KotaTujuan;
use Illuminate\Support\Facades\Validator;

class KotaTujuanController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $cities = KotaTujuan::all(['id', 'name']);

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil',
                'data' => $cities
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching destination cities: ' . $e->getMessage());

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
                'name' => 'required|string|max:255|unique:kota_tujuan,name',
            ],
            [
                'name.required' => 'Nama kota wajib diisi',
                'name.unique' => 'Nama kota sudah ada',
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
            $city = KotaTujuan::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Kota tujuan berhasil dibuat',
                'data' => $city
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error creating destination city: ' . $e->getMessage());

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
                'id' => 'required|integer|exists:kota_tujuan,id',
                'name' => 'required|string|max:255|unique:kota_tujuan,name,' . $request->id,
            ],
            [
                'id.required' => 'ID wajib diisi',
                'id.exists' => 'Kota tidak ditemukan',
                'name.required' => 'Nama kota wajib diisi',
                'name.unique' => 'Nama kota sudah ada',
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
            $city = KotaTujuan::findOrFail($request->id);
            $city->update(['name' => $request->name]);

            return response()->json([
                'status' => 200,
                'message' => 'Kota tujuan berhasil diperbarui',
                'data' => $city
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating destination city: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|integer|exists:kota_tujuan,id',
            ],
            [
                'id.required' => 'ID wajib diisi',
                'id.exists' => 'Kota tidak ditemukan',
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
            $city = KotaTujuan::findOrFail($request->id);
            $city->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Kota tujuan berhasil dihapus',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error deleting destination city: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
}
