<?php

use App\Http\Controllers\Api\Booking\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Master\HotelController;
use App\Http\Controllers\Api\Master\CitiesController;
use App\Http\Controllers\Api\Master\CountryController;
use App\Http\Controllers\Api\Master\PackageController;
use App\Http\Controllers\Api\Master\AirlinesController;
use App\Http\Controllers\Api\Master\KotaTujuanController;
use App\Http\Controllers\Api\Master\PackageTypeController;
use App\Http\Controllers\Api\Master\KotaKeberangkatanController;

Route::prefix('v1')->group(function () {
    Route::post('/Auth', [AuthController::class, 'generateToken']);

    Route::prefix('master')->group(function () {
        Route::get('/airlines', [AirlinesController::class, 'index']);
        Route::get('/country', [CountryController::class, 'index']);
        Route::get('/cities', [CitiesController::class, 'index']);
        Route::get('/hotels', [HotelController::class, 'index']);
        Route::get('/hotel-countries', [HotelController::class, 'getHotelCountries']);
        Route::get('/hotel-cities', [HotelController::class, 'getHotelCities']);
        Route::get('/tipePaket', [PackageTypeController::class, 'index']);
        Route::get('/kotaKeberangkatan', [KotaKeberangkatanController::class, 'index']);
        Route::get('/kotaTujuan', [KotaTujuanController::class, 'index']);

    });

    Route::prefix('booking')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::get('/jamaah', [BookingController::class, 'getAllJamaahWithBooking']);
    });

    Route::prefix('paket')->group(function () {
        Route::get('/', [PackageController::class, 'index']);
        Route::get('/{kodePaket}', [PackageController::class, 'show']);
    });

    Route::middleware('api.auth')->group(function () {
        Route::prefix('master')->group(function () {
            Route::post('/buatAirlines', [AirlinesController::class, 'create']);
            Route::post('/editAirlines', [AirlinesController::class, 'update']);
            Route::post('/buatCountry', [CountryController::class, 'create']);
            Route::post('/editCountry', [CountryController::class, 'update']);
            Route::post('/buatCities', [CitiesController::class, 'create']);
            Route::post('/editCities', [CitiesController::class, 'update']);
            Route::post('/buatTipePaket', [PackageTypeController::class, 'create']);
            Route::post('/editTipePaket', [PackageTypeController::class, 'update']);
            Route::post('/buatHotel', [HotelController::class, 'create']);
            Route::post('/editHotel', [HotelController::class, 'update']);
            Route::post('/deleteHotel', [HotelController::class, 'delete']);
            Route::post('/uploadHotelImage', [HotelController::class, 'uploadHotelImage']);
            Route::post('/editHotelImage', [HotelController::class, 'editHotelImage']);
            Route::post('/deleteHotelImage', [HotelController::class, 'deleteHotelImage']);
            Route::post('/uploadMultipleHotelImage', [HotelController::class, 'uploadMultipleHotelImage']);
            Route::post('/deleteMultipleHotelImage', [HotelController::class, 'deleteMultipleHotelImage']);
            Route::post('/buatKotaKeberangkatan', [KotaKeberangkatanController::class, 'create']);
            Route::post('/editKotaKeberangkatan', [KotaKeberangkatanController::class, 'update']);
            Route::post('/deleteKotaKeberangkatan', [KotaKeberangkatanController::class, 'delete']);
            Route::post('/buatKotaTujuan', [KotaTujuanController::class, 'create']);
            Route::post('/editKotaTujuan', [KotaTujuanController::class, 'update']);
            Route::post('/deleteKotaTujuan', [KotaTujuanController::class, 'delete']);
        });

        Route::prefix('paket')->group(function () {
            Route::post('/buat', [PackageController::class, 'create']);
            Route::post('/update', [PackageController::class, 'update']);
        });

        Route::prefix('booking')->group(function () {
            Route::post('/create', [BookingController::class, 'create']);
            Route::post('/updateSeatTravel', [BookingController::class, 'updateSeatTravel']);

        });
    });
});
