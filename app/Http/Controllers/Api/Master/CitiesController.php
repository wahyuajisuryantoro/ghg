<?php

namespace App\Http\Controllers\Api\Master;

use App\Models\Api\Master\Cities;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CitiesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Cities::with([
                'country' => function ($q) {
                    $q->select('code', 'name');
                }
            ]);

            if ($request->filled('countrycode')) {
                $query->where('countrycode', strtoupper($request->countrycode));
            }

            $cities = $query->get()->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'countrycode' => $city->countrycode,
                    'countryname' => optional($city->country)->name,
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil',
                'data' => $cities
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Error fetching hotel cities: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
    public function create(Request $request): JsonResponse
    {
        $countryCode = strtoupper((string) $request->input('countryCode', ''));

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'countryCode' => 'required|string|exists:country,code',
            ],
            [
                'name.required' => 'Nama city wajib diisi',
                'countryCode.required' => 'Kode negara wajib diisi',
                'countryCode.exists' => 'Kode negara tidak ditemukan',
            ]
        );

        $validator->after(function ($v) use ($request, $countryCode) {
            if ($request->filled('name') && $countryCode !== '') {
                $exists = Cities::where('name', $request->name)
                    ->where('countrycode', $countryCode)
                    ->exists();

                if ($exists) {
                    $v->errors()->add('name', 'Nama city sudah terpakai di negara tersebut');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Data tidak valid',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $city = Cities::create([
                'name' => $request->name,
                'countrycode' => $countryCode,
                'hotel_count' => $request->hotel_count ?? 0,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'City berhasil dibuat',
                'data' => [
                    'id' => $city->id,
                    'name' => $city->name,
                    'countryCode' => $city->countrycode,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error creating city: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        $countryCode = strtoupper((string) $request->input('countryCode', ''));

        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|integer|exists:cities,id',
                'name' => 'required|string|max:255',
                'countryCode' => 'required|string|exists:country,code',
            ],
            [
                'id.required' => 'ID city wajib diisi',
                'id.exists' => 'City tidak ditemukan',
                'name.required' => 'Nama city wajib diisi',
                'countryCode.required' => 'Kode negara wajib diisi',
                'countryCode.exists' => 'Kode negara tidak ditemukan',
            ]
        );

        $validator->after(function ($v) use ($request, $countryCode) {
            if ($request->filled('name') && $countryCode !== '' && $request->filled('id')) {
                $exists = Cities::where('name', $request->name)
                    ->where('countrycode', $countryCode)
                    ->where('id', '!=', $request->id)
                    ->exists();

                if ($exists) {
                    $v->errors()->add('name', 'Nama city sudah terpakai di negara tersebut');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Data tidak valid',
                'data' => $validator->errors()
            ], 422);
        }

        $city = Cities::find($request->id);

        if (!$city) {
            return response()->json([
                'status' => 404,
                'message' => 'City tidak ditemukan',
                'data' => null
            ], 404);
        }

        try {
            $city->update([
                'name' => $request->name,
                'countrycode' => $countryCode,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'City updated successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating city: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
}
