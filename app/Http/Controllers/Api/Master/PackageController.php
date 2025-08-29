<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Models\Api\Master\Hotels;
use Illuminate\Http\JsonResponse;
use App\Models\Api\Master\Packages;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'tipe_paket_id' => 'nullable|integer|exists:package_types,id',
                    'airlines_id' => 'nullable|integer|exists:airlines,idairlines',
                    'hotel_mekka' => 'nullable|integer|exists:hotels,id',
                    'hotel_medina' => 'nullable|integer|exists:hotels,id',
                    'keberangkatan_id' => 'nullable|integer|exists:kota_keberangkatan,id',
                    'kota_tujuan_id' => 'nullable|integer|exists:kota_tujuan,id',
                    'per_page' => 'nullable|integer|min:1|max:100',
                    'page' => 'nullable|integer|min:1',
                ],
                [
                    'tipe_paket_id.exists' => 'Tipe paket tidak ditemukan',
                    'airlines_id.exists' => 'Airlines tidak ditemukan',
                    'hotel_mekka.exists' => 'Hotel Mekka tidak ditemukan',
                    'hotel_medina.exists' => 'Hotel Medina tidak ditemukan',
                    'keberangkatan_id.exists' => 'Kota keberangkatan tidak ditemukan',
                    'kota_tujuan_id.exists' => 'Kota tujuan tidak ditemukan',
                    'per_page.max' => 'Maksimal 100 data per halaman',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Data tidak valid',
                    'data' => $validator->errors()
                ], 422);
            }

            $query = Packages::with([
                'packageType',
                'airline',
                'hotelMekka.city',
                'hotelMedina.city',
                'hotelJedda.city',
                'kotaKeberangkatan',
                'kotaTujuan'
            ]);

            if ($request->filled('tipe_paket_id')) {
                $query->where('tipe_paket_id', $request->tipe_paket_id);
            }

            if ($request->filled('airlines_id')) {
                $query->where('airlines_id', $request->airlines_id);
            }

            if ($request->filled('hotel_mekka')) {
                $query->where('hotel_mekka', $request->hotel_mekka);
            }

            if ($request->filled('hotel_medina')) {
                $query->where('hotel_medina', $request->hotel_medina);
            }

            if ($request->filled('keberangkatan_id')) {
                $query->where('keberangkatan_id', $request->keberangkatan_id);
            }

            if ($request->filled('kota_tujuan_id')) {
                $query->where('kota_tujuan_id', $request->kota_tujuan_id);
            }

            $perPage = $request->get('per_page', 15);
            $packages = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $transformedData = $packages->getCollection()->map(function ($package) {
                return [
                    'kode_paket' => $package->kode_paket,
                    'tipe_paket' => [
                        'id' => $package->packageType->id ?? null,
                        'name' => $package->packageType->name ?? null,
                    ],
                    'total_kursi' => $package->total_kursi,
                    'airline' => [
                        'id' => $package->airline->idairlines ?? null,
                        'name' => $package->airline->airlinesname ?? null,
                    ],
                    'jumlah_hari' => $package->jumlah_hari,
                    'hotels' => [
                        'mekka' => [
                            'id' => $package->hotelMekka->id ?? null,
                            'name' => $package->hotelMekka->name ?? null,
                            'city' => $package->hotelMekka->city->name ?? null,
                        ],
                        'medina' => [
                            'id' => $package->hotelMedina->id ?? null,
                            'name' => $package->hotelMedina->name ?? null,
                            'city' => $package->hotelMedina->city->name ?? null,
                        ],
                        'jedda' => $package->hotel_jedda ? [
                            'id' => $package->hotelJedda->id ?? null,
                            'name' => $package->hotelJedda->name ?? null,
                            'city' => $package->hotelJedda->city->name ?? null,
                        ] : null,
                    ],
                    'kota_keberangkatan' => [
                        'id' => $package->kotaKeberangkatan->id ?? null,
                        'name' => $package->kotaKeberangkatan->name ?? null,
                    ],
                    'kota_tujuan' => [
                        'id' => $package->kotaTujuan->id ?? null,
                        'name' => $package->kotaTujuan->name ?? null,
                    ],
                    'no_penerbangan' => $package->no_penerbangan,
                    'tanggal_berangkat' => $package->tanggal_berangkat,
                    'kurs_tetap' => $package->kurs_tetap,
                    'harga' => [
                        'ber2' => $package->hargaber2,
                        'ber3' => $package->hargaber3,
                        'ber4' => $package->hargaber4,
                        'bayi' => $package->hargabayi,
                    ],
                    'created_at' => $package->created_at,
                    'updated_at' => $package->updated_at,
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Data paket berhasil diambil',
                'data' => $transformedData,
                'meta' => [
                    'current_page' => $packages->currentPage(),
                    'last_page' => $packages->lastPage(),
                    'per_page' => $packages->perPage(),
                    'total' => $packages->total(),
                    'from' => $packages->firstItem(),
                    'to' => $packages->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching packages: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function show($kodePaket): JsonResponse
    {
        try {
            $package = Packages::with([
                'packageType',
                'airline',
                'hotelMekka.city',
                'hotelMedina.city',
                'hotelJedda.city',
                'kotaKeberangkatan',
                'kotaTujuan'
            ])->where('kode_paket', $kodePaket)->first();

            if (!$package) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Paket tidak ditemukan',
                    'data' => null
                ], 404);
            }

            $packageDetail = [
                'kode_paket' => $package->kode_paket,
                'tipe_paket' => [
                    'id' => $package->packageType->id ?? null,
                    'name' => $package->packageType->name ?? null,
                    'category' => $package->packageType->category ?? null,
                ],
                'seat_info' => [
                    'total_kursi' => $package->total_kursi,
                    'total_booked' => $package->total_booked ?? 0,
                    'booked_ppiu' => $package->booked_ppiu ?? 0,
                    'booked_ghg' => $package->booked_ghg ?? 0,
                    'sisa_seat' => $package->sisa_seat ?? $package->total_kursi,
                ],
                'airline' => [
                    'id' => $package->airline->idairlines ?? null,
                    'name' => $package->airline->airlinesname ?? null,
                ],
                'jumlah_hari' => $package->jumlah_hari,
                'hotels' => [
                    'mekka' => [
                        'id' => $package->hotelMekka->id ?? null,
                        'name' => $package->hotelMekka->name ?? null,
                        'city' => $package->hotelMekka->city->name ?? null,
                    ],
                    'medina' => [
                        'id' => $package->hotelMedina->id ?? null,
                        'name' => $package->hotelMedina->name ?? null,
                        'city' => $package->hotelMedina->city->name ?? null,
                    ],
                    'jedda' => $package->hotel_jedda ? [
                        'id' => $package->hotelJedda->id ?? null,
                        'name' => $package->hotelJedda->name ?? null,
                        'city' => $package->hotelJedda->city->name ?? null,
                    ] : null,
                ],
                'lokasi' => [
                    'keberangkatan' => [
                        'id' => $package->kotaKeberangkatan->id ?? null,
                        'name' => $package->kotaKeberangkatan->name ?? null,
                    ],
                    'tujuan' => [
                        'id' => $package->kotaTujuan->id ?? null,
                        'name' => $package->kotaTujuan->name ?? null,
                    ],
                ],
                'penerbangan' => [
                    'no_penerbangan' => $package->no_penerbangan,
                    'tanggal_berangkat' => $package->tanggal_berangkat,
                ],
                'harga' => [
                    'kurs_tetap' => $package->kurs_tetap,
                    'ber2' => $package->hargaber2,
                    'ber3' => $package->hargaber3,
                    'ber4' => $package->hargaber4,
                    'bayi' => $package->hargabayi,
                ],
                'timestamps' => [
                    'created_at' => $package->created_at,
                    'updated_at' => $package->updated_at,
                ],
            ];

            return response()->json([
                'status' => 200,
                'message' => 'Detail paket berhasil diambil',
                'data' => $packageDetail
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching package detail: ' . $e->getMessage());

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
                'tipe_paket_id' => 'required|integer|exists:package_types,id',
                'total_kursi' => 'required|integer|min:1',
                'airlines_id' => 'required|integer|exists:airlines,idairlines',
                'jumlah_hari' => 'required|integer|min:1',
                'hotel_mekka' => 'required|integer|exists:hotels,id',
                'hotel_medina' => 'required|integer|exists:hotels,id',
                'hotel_jedda' => 'nullable|integer|exists:hotels,id',
                'keberangkatan_id' => 'required|integer|exists:kota_keberangkatan,id',
                'kota_tujuan_id' => 'required|integer|exists:kota_tujuan,id',
                'no_penerbangan' => 'required|string|max:255',
                'tanggal_berangkat' => 'required|date|after:today',
                'kurs_tetap' => 'required|integer|min:1',
                'hargaber2' => 'required|numeric|min:0',
                'hargaber3' => 'required|numeric|min:0',
                'hargaber4' => 'required|numeric|min:0',
                'hargabayi' => 'required|numeric|min:0',
            ],
            [
                'tipe_paket_id.required' => 'Tipe paket wajib diisi',
                'tipe_paket_id.exists' => 'Tipe paket tidak ditemukan',
                'total_kursi.required' => 'Total kursi wajib diisi',
                'total_kursi.min' => 'Total kursi minimal 1',
                'airlines_id.required' => 'Airlines wajib diisi',
                'airlines_id.exists' => 'Airlines tidak ditemukan',
                'hotel_mekka.required' => 'Hotel Mekka wajib diisi',
                'hotel_mekka.exists' => 'Hotel Mekka tidak ditemukan',
                'hotel_medina.required' => 'Hotel Medina wajib diisi',
                'hotel_medina.exists' => 'Hotel Medina tidak ditemukan',
                'hotel_jedda.exists' => 'Hotel Jedda tidak ditemukan',
                'keberangkatan_id.required' => 'Kota keberangkatan wajib diisi',
                'keberangkatan_id.exists' => 'Kota keberangkatan tidak ditemukan',
                'kota_tujuan_id.required' => 'Kota tujuan wajib diisi',
                'kota_tujuan_id.exists' => 'Kota tujuan tidak ditemukan',
                'no_penerbangan.required' => 'Nomor penerbangan wajib diisi',
                'tanggal_berangkat.required' => 'Tanggal berangkat wajib diisi',
                'tanggal_berangkat.after' => 'Tanggal berangkat harus setelah hari ini',
                'kurs_tetap.required' => 'Kurs tetap wajib diisi',
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
            $hotelMekka = Hotels::with('city')->find($request->hotel_mekka);
            if ($hotelMekka->city->name !== 'Mekka') {
                return response()->json([
                    'status' => 422,
                    'message' => 'Hotel Mekka harus berlokasi di kota Mekka',
                    'data' => null
                ], 422);
            }

            $hotelMedina = Hotels::with('city')->find($request->hotel_medina);
            if ($hotelMedina->city->name !== 'Madinah') {
                return response()->json([
                    'status' => 422,
                    'message' => 'Hotel Medina harus berlokasi di kota Madinah',
                    'data' => null
                ], 422);
            }

            $kodePacket = $this->generateKodePacket();

            $package = Packages::create([
                'kode_paket' => $kodePacket,
                'tipe_paket_id' => $request->tipe_paket_id,
                'total_kursi' => $request->total_kursi,
                'airlines_id' => $request->airlines_id,
                'jumlah_hari' => $request->jumlah_hari,
                'hotel_mekka' => $request->hotel_mekka,
                'hotel_medina' => $request->hotel_medina,
                'hotel_jedda' => $request->hotel_jedda,
                'keberangkatan_id' => $request->keberangkatan_id,
                'kota_tujuan_id' => $request->kota_tujuan_id,
                'no_penerbangan' => $request->no_penerbangan,
                'tanggal_berangkat' => $request->tanggal_berangkat,
                'kurs_tetap' => $request->kurs_tetap,
                'hargaber2' => $request->hargaber2,
                'hargaber3' => $request->hargaber3,
                'hargaber4' => $request->hargaber4,
                'hargabayi' => $request->hargabayi,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Paket Berhasil Dibuat',
                'data' => [
                    'kode_paket' => $package->kode_paket,
                    'ghg' => [
                        'status' => 'success',
                        'message' => 'Paket berhasil dikirim ke GHG'
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error creating package: ' . $e->getMessage());

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
                'kode_paket' => 'required|string|exists:packages,kode_paket',
                'tipe_paket_id' => 'nullable|integer|exists:package_types,id',
                'total_kursi' => 'nullable|integer|min:1',
                'airlines_id' => 'nullable|integer|exists:airlines,idairlines',
                'jumlah_hari' => 'nullable|integer|min:1',
                'hotel_mekka' => 'nullable|integer|exists:hotels,id',
                'hotel_medina' => 'nullable|integer|exists:hotels,id',
                'hotel_jedda' => 'nullable|integer|exists:hotels,id',
                'keberangkatan_id' => 'nullable|integer|exists:kota_keberangkatan,id',
                'kota_tujuan_id' => 'nullable|integer|exists:kota_tujuan,id',
                'no_penerbangan' => 'nullable|string|max:255',
                'tanggal_berangkat' => 'nullable|date',
                'kurs_tetap' => 'nullable|integer|min:1',
                'hargaber2' => 'nullable|numeric|min:0',
                'hargaber3' => 'nullable|numeric|min:0',
                'hargaber4' => 'nullable|numeric|min:0',
                'hargabayi' => 'nullable|numeric|min:0',
            ],
            [
                'kode_paket.required' => 'Kode paket wajib diisi',
                'kode_paket.exists' => 'Paket tidak ditemukan',
                'tipe_paket_id.exists' => 'Tipe paket tidak ditemukan',
                'airlines_id.exists' => 'Airlines tidak ditemukan',
                'hotel_mekka.exists' => 'Hotel Mekka tidak ditemukan',
                'hotel_medina.exists' => 'Hotel Medina tidak ditemukan',
                'hotel_jedda.exists' => 'Hotel Jedda tidak ditemukan',
                'keberangkatan_id.exists' => 'Kota keberangkatan tidak ditemukan',
                'kota_tujuan_id.exists' => 'Kota tujuan tidak ditemukan',
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
            $package = Packages::where('kode_paket', $request->kode_paket)->first();

            if ($request->has('hotel_mekka')) {
                $hotelMekka = Hotels::with('city')->find($request->hotel_mekka);
                if ($hotelMekka->city->name !== 'Mekka') {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Hotel Mekka harus berlokasi di kota Mekka',
                        'data' => null
                    ], 422);
                }
            }

            if ($request->has('hotel_medina')) {
                $hotelMedina = Hotels::with('city')->find($request->hotel_medina);
                if ($hotelMedina->city->name !== 'Madinah') {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Hotel Medina harus berlokasi di kota Madinah',
                        'data' => null
                    ], 422);
                }
            }

            $updateData = $request->only([
                'tipe_paket_id',
                'total_kursi',
                'airlines_id',
                'jumlah_hari',
                'hotel_mekka',
                'hotel_medina',
                'hotel_jedda',
                'keberangkatan_id',
                'kota_tujuan_id',
                'no_penerbangan',
                'tanggal_berangkat',
                'kurs_tetap',
                'hargaber2',
                'hargaber3',
                'hargaber4',
                'hargabayi'
            ]);

            $package->update(array_filter($updateData, function ($value) {
                return !is_null($value);
            }));

            return response()->json([
                'status' => 200,
                'message' => 'Paket Berhasil Diperbarui',
                'data' => [
                    'kode_paket' => $package->kode_paket,
                    'ghg' => [
                        'status' => 'success',
                        'message' => 'Paket berhasil diupdate di GHG'
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating package: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    private function generateKodePacket(): string
    {
        $prefix = 'UMR';
        $number = str_pad(Packages::count() + 1, 3, '0', STR_PAD_LEFT);
        return $prefix . $number;
    }
}
