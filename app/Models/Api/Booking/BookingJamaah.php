<?php

namespace App\Models\Api\Booking;

use Illuminate\Database\Eloquent\Model;

class BookingJamaah extends Model
{
    protected $fillable = [
        'kode_booking',
        'jamaah_id',
        'main_jamaah_id',
        'is_main_jamaah',
        'hubungan_dengan_main',
        'harga_paket',
        'status',
    ];

    protected $casts = [
        'is_main_jamaah' => 'boolean',
        'harga_paket' => 'decimal:2',
    ];

    // Relasi ke booking
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'kode_booking', 'kode_booking');
    }

    // Relasi ke jamaah
    public function jamaah()
    {
        return $this->belongsTo(Jamaah::class, 'jamaah_id');
    }

    public function mainJamaah()
    {
        return $this->belongsTo(Jamaah::class, 'main_jamaah_id');
    }

    public function jamaahDibawa()
    {
        return $this->hasMany(self::class, 'main_jamaah_id', 'jamaah_id')
            ->where('kode_booking', $this->kode_booking);
    }
}
