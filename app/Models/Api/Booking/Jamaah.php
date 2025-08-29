<?php

namespace App\Models\Api\Booking;

use Illuminate\Database\Eloquent\Model;

class Jamaah extends Model
{
   protected $fillable = [
        'nama',
        'jenis_kelamin',
        'no_ktp',
        'no_passport',
        'tanggal_lahir',
        'tempat_lahir',
        'alamat',
        'no_telepon',
        'email',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_jamaahs', 'jamaah_id', 'kode_booking', 'id', 'kode_booking')
                    ->withPivot(['is_main_jamaah', 'hubungan_dengan_main', 'harga_paket', 'status'])
                    ->withTimestamps();
    }
    public function mainBookings()
    {
        return $this->hasMany(Booking::class, 'main_jamaah_id');
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nama'] = ucwords(strtolower($value));
    }

    public function getUmurAttribute()
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }
}
