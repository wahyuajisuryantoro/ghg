<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Models\Api\Master\Cities;
use App\Models\Api\Master\Hotels;
use Illuminate\Http\JsonResponse;
use App\Models\Api\Master\Country;
use App\Http\Controllers\Controller;
use App\Models\Api\Master\HotelImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HotelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Hotels::with(['city.country', 'images']);
            if ($request->has('idcity') && $request->idcity) {
                $query->where('idhotelcity', $request->idcity);
            }
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('hotelname', 'LIKE', "%{$search}%")
                        ->orWhere('hoteladdress', 'LIKE', "%{$search}%")
                        ->orWhere('notes', 'LIKE', "%{$search}%");
                });
            }

            $total = $query->count();

            $limit = $request->limit ?? 100;
            $offset = $request->offset ?? 0;

            $hotels = $query->skip($offset)
                ->take($limit)
                ->get();

            $hotelData = $hotels->map(function ($hotel) {
                return [
                    'idhotel' => $hotel->id,
                    'hotelname' => $hotel->hotelname,
                    'idhotelcity' => $hotel->idhotelcity,
                    'cityname' => $hotel->city->name ?? null,
                    'countrycode' => $hotel->city->country->code ?? null,
                    'hoteladdress' => $hotel->hoteladdress,
                    'notes' => $hotel->notes,
                    'bintang' => $hotel->bintang,
                    'jarak' => $hotel->jarak,
                    'hotellat' => $hotel->hotellat,
                    'hotellong' => $hotel->hotellong,
                    'preview_image' => $hotel->images->first() ? url($hotel->images->first()->urlimage) : null,
                    'image_count' => $hotel->images->count(),
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil',
                'data' => [
                    'total' => $total,
                    'offset' => (int) $offset,
                    'limit' => (int) $limit,
                    'hotels' => $hotelData
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching hotels: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
    public function getHotelCountries(): JsonResponse
    {
        try {
            $countries = Country::select('country.code', 'country.name')
                ->selectRaw('COUNT(DISTINCT hotels.id) as hotel_count')
                ->selectRaw('COUNT(DISTINCT cities.id) as city_count')
                ->join('cities', 'country.code', '=', 'cities.countrycode')
                ->join('hotels', 'cities.id', '=', 'hotels.idhotelcity')
                ->groupBy('country.code', 'country.name')
                ->having('hotel_count', '>', 0)
                ->get();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil',
                'data' => $countries
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching hotel countries: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function getHotelCities(Request $request): JsonResponse
    {
        try {
            $query = Cities::select('cities.id', 'cities.name', 'cities.countrycode', 'country.name as countryname')
                ->selectRaw('COUNT(hotels.id) as hotel_count')
                ->join('country', 'cities.countrycode', '=', 'country.code')
                ->join('hotels', 'cities.id', '=', 'hotels.idhotelcity');

            if ($request->has('countrycode') && $request->countrycode) {
                $query->where('cities.countrycode', $request->countrycode);
            }

            $cities = $query->groupBy('cities.id', 'cities.name', 'cities.countrycode', 'country.name')
                ->having('hotel_count', '>', 0)
                ->get();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil',
                'data' => $cities
            ], 200);

        } catch (\Exception $e) {
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
        $validator = Validator::make(
            $request->all(),
            [
                'hotelname' => 'required|string|max:255',
                'idhotelcity' => 'required|integer|exists:cities,id',
                'hoteladdress' => 'nullable|string',
                'notes' => 'nullable|string',
                'bintang' => 'nullable|integer|min:1|max:5',
                'jarak' => 'nullable|integer',
                'hotellat' => 'nullable|numeric|between:-90,90',
                'hotellong' => 'nullable|numeric|between:-180,180',
            ],
            [
                'hotelname.required' => 'Nama hotel wajib diisi',
                'idhotelcity.required' => 'ID kota hotel wajib diisi',
                'idhotelcity.exists' => 'Kota hotel tidak ditemukan',
                'bintang.between' => 'Rating bintang harus antara 1-5',
                'hotellat.between' => 'Latitude harus antara -90 sampai 90',
                'hotellong.between' => 'Longitude harus antara -180 sampai 180',
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
            $hotel = Hotels::create($request->all());

            return response()->json([
                'status' => 200,
                'message' => 'Hotel berhasil ditambahkan',
                'data' => $hotel
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error creating hotel: ' . $e->getMessage());

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
                'idhotel' => 'required|integer|exists:hotels,id',
                'hotelname' => 'nullable|string|max:255',
                'idhotelcity' => 'nullable|integer|exists:cities,id',
                'hoteladdress' => 'nullable|string',
                'notes' => 'nullable|string',
                'bintang' => 'nullable|integer|min:1|max:5',
                'jarak' => 'nullable|integer',
                'hotellat' => 'nullable|numeric|between:-90,90',
                'hotellong' => 'nullable|numeric|between:-180,180',
            ],
            [
                'idhotel.required' => 'ID hotel wajib diisi',
                'idhotel.exists' => 'Hotel tidak ditemukan',
                'idhotelcity.exists' => 'Kota hotel tidak ditemukan',
                'bintang.between' => 'Rating bintang harus antara 1-5',
                'hotellat.between' => 'Latitude harus antara -90 sampai 90',
                'hotellong.between' => 'Longitude harus antara -180 sampai 180',
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
            $hotel = Hotels::findOrFail($request->idhotel);

            $updateData = $request->only([
                'hotelname',
                'idhotelcity',
                'hoteladdress',
                'notes',
                'bintang',
                'jarak',
                'hotellat',
                'hotellong'
            ]);

            $hotel->update(array_filter($updateData, function ($value) {
                return !is_null($value);
            }));

            return response()->json([
                'status' => 200,
                'message' => 'Hotel berhasil diperbarui',
                'data' => $hotel->fresh()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating hotel: ' . $e->getMessage());

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
                'idhotel' => 'required|integer|exists:hotels,id',
            ],
            [
                'idhotel.required' => 'ID hotel wajib diisi',
                'idhotel.exists' => 'Hotel tidak ditemukan',
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
            $hotel = Hotels::findOrFail($request->idhotel);
            $hotel->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Hotel berhasil dihapus',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error deleting hotel: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function uploadHotelImage(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'idhotel' => 'required|integer|exists:hotels,id',
                'fasilitas' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:8120',
            ],
            [
                'idhotel.required' => 'ID hotel wajib diisi',
                'idhotel.exists' => 'Hotel tidak ditemukan',
                'fasilitas.required' => 'Fasilitas hotel wajib diisi',
                'image.required' => 'File gambar wajib diisi',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
                'image.max' => 'Ukuran file maksimal 8MB',
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
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'assets/images/hotel/' . $filename;

            $image->storeAs('assets/images/hotel', $filename, 'public');

            $hotelImage = HotelImage::create([
                'idhotel' => $request->idhotel,
                'fasilitas' => $request->fasilitas,
                'urlimage' => $path,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil mengunggah gambar',
                'data' => [
                    'idhotelimage' => $hotelImage->idhotelimage,
                    'idhotel' => $hotelImage->idhotel,
                    'fasilitas' => $hotelImage->fasilitas,
                    'urlimage' => $hotelImage->urlimage,
                    'full_url' => $hotelImage->full_url,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error uploading hotel image: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function editHotelImage(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|integer|exists:hotel_images,id',
                'idhotel' => 'nullable|integer|exists:hotels,id',
                'fasilitas' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            ],
            [
                'id.required' => 'ID hotel image wajib diisi',
                'id.exists' => 'Hotel image tidak ditemukan',
                'idhotel.exists' => 'Hotel tidak ditemukan',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
                'image.max' => 'Ukuran file maksimal 5MB',
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
            $hotelImage = HotelImage::findOrFail($request->id);
            if ($request->has('idhotel')) {
                $hotelImage->idhotel = $request->idhotel;
            }

            if ($request->has('fasilitas')) {
                $hotelImage->fasilitas = $request->fasilitas;
            }

            if ($request->hasFile('image')) {
                if ($hotelImage->urlimage && Storage::disk('public')->exists($hotelImage->urlimage)) {
                    Storage::disk('public')->delete($hotelImage->urlimage);
                }
                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = 'assets/images/hotel/' . $filename;

                $image->storeAs('assets/images/hotel', $filename, 'public');
                $hotelImage->urlimage = $path;
            }

            $hotelImage->save();

            return response()->json([
                'status' => 200,
                'message' => 'Hotel image berhasil diperbarui',
                'data' => [
                    'id' => $hotelImage->id,
                    'idhotel' => $hotelImage->idhotel,
                    'fasilitas' => $hotelImage->fasilitas,
                    'urlimage' => $hotelImage->urlimage,
                    'full_url' => $hotelImage->full_url,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating hotel image: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function deleteHotelImage(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|integer|exists:hotel_images,id',
            ],
            [
                'id.required' => 'ID hotel image wajib diisi',
                'id.exists' => 'Hotel image tidak ditemukan',
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
            $hotelImage = HotelImage::findOrFail($request->id);
            if ($hotelImage->urlimage && Storage::disk('public')->exists($hotelImage->urlimage)) {
                Storage::disk('public')->delete($hotelImage->urlimage);
            }
            $hotelImage->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Foto hotel berhasil dihapus',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error deleting hotel image: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function uploadMultipleHotelImage(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'idhotel' => 'required|integer|exists:hotels,id',
                'fasilitas' => 'required|string|max:255',
                'images' => 'required|array|min:1',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            ],
            [
                'idhotel.required' => 'ID hotel wajib diisi',
                'idhotel.exists' => 'Hotel tidak ditemukan',
                'fasilitas.required' => 'Fasilitas hotel wajib diisi',
                'images.required' => 'File gambar wajib diisi',
                'images.array' => 'Images harus berupa array',
                'images.min' => 'Minimal 1 gambar harus diupload',
                'images.*.image' => 'File harus berupa gambar',
                'images.*.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
                'images.*.max' => 'Ukuran file maksimal 5MB per gambar',
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
            $uploadedImages = [];
            $failedUploads = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($request->file('images') as $image) {
                try {
                    $originalFilename = $image->getClientOriginalName();
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = 'assets/images/hotel/' . $filename;
                    $image->storeAs('assets/images/hotel', $filename, 'public');
                    $hotelImage = HotelImage::create([
                        'idhotel' => $request->idhotel,
                        'fasilitas' => $request->fasilitas,
                        'urlimage' => $path,
                    ]);

                    $uploadedImages[] = [
                        'id' => $hotelImage->id,
                        'idhotel' => $hotelImage->idhotel,
                        'fasilitas' => $hotelImage->fasilitas,
                        'urlimage' => $hotelImage->urlimage,
                        'full_url' => $hotelImage->full_url,
                        'original_filename' => $originalFilename,
                    ];

                    $successCount++;

                } catch (\Exception $e) {
                    $failedUploads[] = [
                        'original_filename' => $image->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ];
                    $failedCount++;
                }
            }

            $totalImages = $successCount + $failedCount;
            $message = "Berhasil mengunggah {$successCount} gambar";
            if ($failedCount > 0) {
                $message .= ", gagal {$failedCount} gambar";
            }

            return response()->json([
                'status' => 200,
                'message' => $message,
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'uploaded_images' => $uploadedImages,
                    'failed_uploads' => $failedUploads,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error uploading multiple hotel images: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }

    public function deleteMultipleHotelImage(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer',
            ],
            [
                'ids.required' => 'Array ID gambar hotel wajib diisi',
                'ids.array' => 'IDs harus berupa array',
                'ids.min' => 'Minimal 1 ID harus diisi',
                'ids.*.required' => 'ID tidak boleh kosong',
                'ids.*.integer' => 'ID harus berupa angka',
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
            $ids = $request->ids;
            $details = [];
            $successCount = 0;
            $failedCount = 0;
            $notFoundCount = 0;

            foreach ($ids as $id) {
                try {
                    $hotelImage = HotelImage::find($id);

                    if (!$hotelImage) {
                        $details[] = [
                            'id' => $id,
                            'status' => 'not_found',
                            'message' => 'Foto hotel tidak ditemukan'
                        ];
                        $notFoundCount++;
                        continue;
                    }

                    if ($hotelImage->urlimage && Storage::disk('public')->exists($hotelImage->urlimage)) {
                        Storage::disk('public')->delete($hotelImage->urlimage);
                    }

                    $hotelImage->delete();

                    $details[] = [
                        'id' => $id,
                        'status' => 'success',
                        'message' => 'Foto hotel berhasil dihapus'
                    ];
                    $successCount++;

                } catch (\Exception $e) {
                    $details[] = [
                        'id' => $id,
                        'status' => 'failed',
                        'message' => 'Gagal menghapus foto hotel: ' . $e->getMessage()
                    ];
                    $failedCount++;
                }
            }

            $total = count($ids);

            return response()->json([
                'status' => 200,
                'message' => 'Proses penghapusan foto hotel selesai',
                'data' => [
                    'summary' => [
                        'total' => $total,
                        'success' => $successCount,
                        'failed' => $failedCount,
                        'not_found' => $notFoundCount
                    ],
                    'details' => $details
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error deleting multiple hotel images: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan server',
                'data' => null
            ], 500);
        }
    }
}
