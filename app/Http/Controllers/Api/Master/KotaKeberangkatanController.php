<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Api\Master\KotaKeberangkatan;

class KotaKeberangkatanController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $cities = KotaKeberangkatan::all(['id', 'name']);

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil',
                'data' => $cities
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching departure cities: ' . $e->getMessage());

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
                'name' => 'required|string|max:255|unique:kota_keberangkatan,name',
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
            $city = KotaKeberangkatan::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Kota keberangkatan berhasil dibuat',
                'data' => $city
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error creating departure city: ' . $e->getMessage());

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
                'id' => 'required|integer|exists:kota_keberangkatan,id',
                'name' => 'required|string|max:255|unique:kota_keberangkatan,name,' . $request->id,
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
            $city = KotaKeberangkatan::findOrFail($request->id);
            $city->update(['name' => $request->name]);

            return response()->json([
                'status' => 200,
                'message' => 'Kota keberangkatan berhasil diperbarui',
                'data' => $city
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating departure city: ' . $e->getMessage());

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
                'id' => 'required|integer|exists:kota_keberangkatan,id',
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
            $city = KotaKeberangkatan::findOrFail($request->id);
            $city->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Kota keberangkatan berhasil dihapus',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error deleting departure city: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
}
