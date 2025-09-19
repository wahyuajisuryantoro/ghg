<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Api\Master\Country;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $countries = Country::all(['code', 'name']);

            return response()->json([
                'status' => 200,
                'message' => 'Daftar Country berhasil diambil',
                'data' => $countries
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching countries: ' . $e->getMessage());

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
                'code' => 'required|string|max:5|unique:country,code',
                'name' => 'required|string|max:255|unique:country,name',
            ],
            [
                'code.required' => 'Kode country wajib diisi',
                'code.unique' => 'Kode country sudah terpakai',
                'name.required' => 'Nama country wajib diisi',
                'name.unique' => 'Nama country sudah terpakai',
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
            $country = Country::create([
                'code' => strtoupper($request->code),
                'name' => $request->name,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Country berhasil dibuat',
                'data' => [
                    'country' => $country
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error creating country: ' . $e->getMessage());

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
                'code' => 'required|string|exists:country,code',
                'new_code' => 'required|string|max:5|unique:country,code,' . $request->code . ',code',
                'name' => 'required|string|max:255|unique:country,name,' . $request->code . ',code'
            ],
            [
                'new_code.unique' => 'Kode country sudah terpakai',
                'new_code.required' => 'Kode country wajib diisi',
                'name.unique' => 'Nama country sudah terpakai',
                'name.required' => 'Nama country wajib diisi'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Data tidak valid',
                'data' => $validator->errors()
            ], 422);
        }

        $country = Country::where('code', $request->code)->first();

        if (!$country) {
            return response()->json([
                'status' => 404,
                'message' => 'Country tidak ditemukan',
                'data' => null
            ], 404);
        }

        try {
            $country->update([
                'code' => strtoupper($request->new_code),
                'name' => $request->name
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Country updated successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating country: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
}
