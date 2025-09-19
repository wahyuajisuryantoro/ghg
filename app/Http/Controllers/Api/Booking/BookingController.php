<?php

namespace App\Http\Controllers\Api\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Api\Booking\Jamaah;
use Illuminate\Support\Facades\DB;
use App\Models\Api\Booking\Booking;
use App\Models\Api\Master\Packages;
use App\Http\Controllers\Controller;
use App\Models\Api\Booking\BookingJamaah;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:pending,confirmed,paid,cancelled',
                'kode_paket' => 'nullable|string|exists:packages,kode_paket',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Data tidak valid',
                    'data' => $validator->errors()
                ], 422);
            }

            $query = Booking::with(['package', 'mainJamaah']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('kode_paket')) {
                $query->where('kode_paket', $request->kode_paket);
            }

            $perPage = $request->get('per_page', 15);
            $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $transformedData = $bookings->getCollection()->map(function ($booking) {
                return [
                    'kode_booking' => $booking->kode_booking,
                    'kode_paket' => $booking->kode_paket,
                    'package_info' => [
                        'kode_paket' => $booking->package->kode_paket ?? null,
                        'tanggal_berangkat' => $booking->package->tanggal_berangkat ?? null,
                        'jumlah_hari' => $booking->package->jumlah_hari ?? null,
                    ],
                    'main_jamaah' => [
                        'id' => $booking->mainJamaah->id ?? null,
                        'nama' => $booking->mainJamaah->nama ?? null,
                        'no_ktp' => $booking->mainJamaah->no_ktp ?? null,
                        'no_telepon' => $booking->mainJamaah->no_telepon ?? null,
                        'email' => $booking->mainJamaah->email ?? null,
                    ],
                    'total_jamaah' => $booking->total_jamaah,
                    'total_harga' => $booking->total_harga,
                    'status' => $booking->status,
                    'notes' => $booking->notes,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Data booking berhasil diambil',
                'data' => $transformedData,
                'meta' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching bookings: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
    public function updateSeatTravel(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kode_paket' => 'required|string|exists:packages,kode_paket',
                'total_seat_travel' => 'required|integer|min:0',
            ],
            [
                'kode_paket.required' => 'Kode paket wajib diisi',
                'kode_paket.exists' => 'Paket tidak ditemukan',
                'total_seat_travel.required' => 'Total seat travel wajib diisi',
                'total_seat_travel.min' => 'Total seat travel minimal 0',
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
            $booking = Booking::where('kode_paket', $request->kode_paket)->first();

            if (!$booking) {
                $booking = Booking::create([
                    'kode_booking' => 'BK' . str_pad(Booking::count() + 1, 3, '0', STR_PAD_LEFT),
                    'kode_paket' => $request->kode_paket,
                    'total_seat_travel' => $request->total_seat_travel,
                    'booked_ppiu' => 0,
                    'booked_ghg' => 0,
                ]);
            } else {
                $booking->update(['total_seat_travel' => $request->total_seat_travel]);
            }

            $data = [
                'idumrohpackage' => $booking->kode_paket,
                'total_booked' => $booking->total_booked,
                'booked_ppiu' => $booking->booked_ppiu,
                'booked_ghg' => $booking->booked_ghg,
                'sisa_seat' => $booking->sisa_seat,
            ];

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil Update Paket',
                'data' => [$data]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating seat travel: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kode_paket' => 'required|string|exists:packages,kode_paket',
            'jamaah' => 'required|array|min:1|max:10',
            'jamaah.*.nama' => 'required|string|max:255',
            'jamaah.*.jenis_kelamin' => 'required|in:L,P',
            'jamaah.*.no_ktp' => 'required|string|max:20',
            'jamaah.*.no_passport' => 'nullable|string|max:20',
            'jamaah.*.tanggal_lahir' => 'required|date|before:today',
            'jamaah.*.tempat_lahir' => 'nullable|string|max:255',
            'jamaah.*.alamat' => 'nullable|string',
            'jamaah.*.no_telepon' => 'nullable|string|max:20',
            'jamaah.*.email' => 'nullable|email|max:255',
            'jamaah.*.hubungan_dengan_main' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Data tidak valid',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $package = Packages::where('kode_paket', $request->kode_paket)->lockForUpdate()->first();
            $totalJamaah = count($request->jamaah);

            $sisaKursi = $package->total_kursi - $package->total_booked;

            if ($sisaKursi < $totalJamaah) {
                return response()->json([
                    'status' => 422,
                    'message' => "Kursi tidak mencukupi. Sisa kursi: {$sisaKursi}, Dibutuhkan: {$totalJamaah}",
                    'data' => ['sisa_kursi' => $sisaKursi, 'dibutuhkan' => $totalJamaah]
                ], 422);
            }
            $totalJamaahDewasa = 0;
            foreach ($request->jamaah as $jamaahInput) {
                $birthDate = new \Carbon\Carbon($jamaahInput['tanggal_lahir']);
                $age = $birthDate->age;
                if ($age >= 2) {
                    $totalJamaahDewasa++;
                }
            }

            $kodeBooking = Booking::generateKodeBooking();
            $jamaahIds = [];
            $mainJamaahId = null;

            foreach ($request->jamaah as $index => $jamaahInput) {
                $isMainJamaah = ($index === 0);

                $jamaah = Jamaah::where('no_ktp', $jamaahInput['no_ktp'])->first();

                if (!$jamaah) {
                    $jamaah = Jamaah::create([
                        'nama' => $jamaahInput['nama'],
                        'jenis_kelamin' => $jamaahInput['jenis_kelamin'],
                        'no_ktp' => $jamaahInput['no_ktp'],
                        'no_passport' => $jamaahInput['no_passport'] ?? null,
                        'tanggal_lahir' => $jamaahInput['tanggal_lahir'],
                        'tempat_lahir' => $jamaahInput['tempat_lahir'] ?? null,
                        'alamat' => $jamaahInput['alamat'] ?? null,
                        'no_telepon' => $jamaahInput['no_telepon'] ?? null,
                        'email' => $jamaahInput['email'] ?? null,
                    ]);
                }

                $jamaahIds[] = [
                    'jamaah_id' => $jamaah->id,
                    'is_main_jamaah' => $isMainJamaah,
                    'hubungan_dengan_main' => $isMainJamaah ? null : ($jamaahInput['hubungan_dengan_main'] ?? 'Keluarga'),
                ];

                if ($isMainJamaah) {
                    $mainJamaahId = $jamaah->id;
                }
            }
            $booking = Booking::create([
                'kode_booking' => $kodeBooking,
                'kode_paket' => $request->kode_paket,
                'main_jamaah_id' => $mainJamaahId,
                'total_jamaah' => $totalJamaah,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($jamaahIds as $jamaahData) {
                BookingJamaah::create([
                    'kode_booking' => $kodeBooking,
                    'jamaah_id' => $jamaahData['jamaah_id'],
                    'main_jamaah_id' => $jamaahData['is_main_jamaah'] ? null : $mainJamaahId,
                    'is_main_jamaah' => $jamaahData['is_main_jamaah'],
                    'hubungan_dengan_main' => $jamaahData['hubungan_dengan_main'],
                    'status' => 'active',
                ]);
            }
            $newTotalBooked = $package->total_booked + $totalJamaah;
            $newBookedPpiu = $package->booked_ppiu + $totalJamaah;
            $newSisaSeat = $package->total_kursi - $newTotalBooked;

            $package->update([
                'total_booked' => $newTotalBooked,
                'booked_ppiu' => $newBookedPpiu,
                'sisa_seat' => $newSisaSeat
            ]);

            DB::commit();

            $mainJamaah = Jamaah::find($mainJamaahId);

            return response()->json([
                'status' => 200,
                'message' => 'Booking berhasil dibuat',
                'data' => [
                    'kode_booking' => $kodeBooking,
                    'kode_paket' => $request->kode_paket,
                    'total_jamaah' => $totalJamaah,
                    'total_jamaah_dewasa' => $totalJamaahDewasa,
                    'status' => 'pending',
                    'main_jamaah' => [
                        'nama' => $mainJamaah->nama,
                        'no_ktp' => $mainJamaah->no_ktp,
                        'no_telepon' => $mainJamaah->no_telepon,
                    ],
                    'package_seat_info' => [
                        'total_kursi' => $package->total_kursi,
                        'total_booked' => $newTotalBooked,
                        'booked_ppiu' => $newBookedPpiu,
                        'booked_ghg' => $package->booked_ghg,
                        'sisa_seat' => $newSisaSeat
                    ],
                    'harga_breakdown' => "Jamaah dewasa ({$totalJamaahDewasa}): " .
                        ($totalJamaahDewasa == 1 ? "Single" :
                            ($totalJamaahDewasa == 2 ? "Ber-2" :
                                ($totalJamaahDewasa == 3 ? "Ber-3" : "Ber-4")))
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error creating booking: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function getAllJamaahWithBooking(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'no_ktp' => 'nullable|string|max:20',
                'nama' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Data tidak valid',
                    'data' => $validator->errors()
                ], 422);
            }
            $query = BookingJamaah::with(['jamaah', 'booking.package'])
                ->where('is_main_jamaah', true);

            if ($request->filled('no_ktp')) {
                $query->whereHas('jamaah', function ($q) use ($request) {
                    $q->where('no_ktp', 'like', '%' . $request->no_ktp . '%');
                });
            }

            if ($request->filled('nama')) {
                $query->whereHas('jamaah', function ($q) use ($request) {
                    $q->where('nama', 'like', '%' . $request->nama . '%');
                });
            }

            $perPage = $request->get('per_page', 15);
            $mainJamaahs = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $transformedData = $mainJamaahs->getCollection()->map(function ($mainBooking) {
                $jamaah = $mainBooking->jamaah;
                $allBookings = BookingJamaah::with(['booking.package'])
                    ->where('jamaah_id', $jamaah->id)
                    ->where('is_main_jamaah', true)
                    ->get();

                $bookingHistory = $allBookings->map(function ($bookingRecord) use ($jamaah) {
                    $jamaahDibawa = BookingJamaah::with('jamaah')
                        ->where('kode_booking', $bookingRecord->kode_booking)
                        ->where('main_jamaah_id', $jamaah->id)
                        ->where('status', 'active')
                        ->get();

                    return [
                        'kode_booking' => $bookingRecord->kode_booking,
                        'kode_paket' => $bookingRecord->booking->kode_paket,
                        'package_info' => [
                            'tanggal_berangkat' => $bookingRecord->booking->package->tanggal_berangkat ?? null,
                            'jumlah_hari' => $bookingRecord->booking->package->jumlah_hari ?? null,
                        ],
                        'total_harga' => $bookingRecord->booking->total_harga,
                        'status' => $bookingRecord->booking->status,
                        'jamaah_dibawa' => $jamaahDibawa->map(function ($j) {
                            return [
                                'id' => $j->jamaah->id,
                                'nama' => $j->jamaah->nama,
                                'no_ktp' => $j->jamaah->no_ktp,
                                'jenis_kelamin' => $j->jamaah->jenis_kelamin,
                                'hubungan_dengan_main' => $j->hubungan_dengan_main,
                                'harga_paket' => $j->harga_paket,
                            ];
                        }),
                        'total_jamaah_dalam_booking' => $jamaahDibawa->count() + 1,
                        'created_at' => $bookingRecord->booking->created_at,
                    ];
                });

                return [
                    'main_jamaah' => [
                        'id' => $jamaah->id,
                        'nama' => $jamaah->nama,
                        'jenis_kelamin' => $jamaah->jenis_kelamin,
                        'no_ktp' => $jamaah->no_ktp,
                        'no_passport' => $jamaah->no_passport,
                        'tanggal_lahir' => $jamaah->tanggal_lahir->format('Y-m-d'),
                        'tempat_lahir' => $jamaah->tempat_lahir,
                        'alamat' => $jamaah->alamat,
                        'no_telepon' => $jamaah->no_telepon,
                        'email' => $jamaah->email,
                    ],
                    'total_booking_sebagai_main' => $allBookings->count(),
                    'booking_history' => $bookingHistory,
                    'created_at' => $jamaah->created_at,
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Data jamaah dengan riwayat booking berhasil diambil',
                'data' => $transformedData,
                'meta' => [
                    'current_page' => $mainJamaahs->currentPage(),
                    'last_page' => $mainJamaahs->lastPage(),
                    'per_page' => $mainJamaahs->perPage(),
                    'total' => $mainJamaahs->total(),
                    'from' => $mainJamaahs->firstItem(),
                    'to' => $mainJamaahs->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching jamaah with booking: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }


    private function calculateJamaahPrice(Packages $package, array $jamaahData, int $totalJamaahDewasa): float
    {
        $birthDate = new \Carbon\Carbon($jamaahData['tanggal_lahir']);
        $age = $birthDate->age;

        if ($age < 2) {
            return (float) $package->hargabayi;
        }

        switch ($totalJamaahDewasa) {
            case 1:
                return (float) $package->hargaber1;
            case 2:
                return (float) $package->hargaber2;
            case 3:
                return (float) $package->hargaber3;
            case 4:
            default:
                return (float) $package->hargaber4;
        }
    }
}
