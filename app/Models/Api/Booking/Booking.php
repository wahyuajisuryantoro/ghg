<?php

namespace App\Models\Api\Booking;

use App\Models\Api\Master\Packages;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
   protected $fillable = [
        'kode_booking',
        'kode_paket',
        'main_jamaah_id',
        'total_jamaah',
        'total_harga',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
    ];

    public function package()
    {
        return $this->belongsTo(Packages::class, 'kode_paket', 'kode_paket');
    }

    public function mainJamaah()
    {
        return $this->belongsTo(Jamaah::class, 'main_jamaah_id');
    }

    public function jamaahs()
    {
        return $this->belongsToMany(Jamaah::class, 'booking_jamaahs', 'kode_booking', 'jamaah_id', 'kode_booking', 'id')
                    ->withPivot(['is_main_jamaah', 'hubungan_dengan_main', 'harga_paket', 'status'])
                    ->withTimestamps();
    }

    public static function generateKodeBooking()
    {
        $prefix = 'BK';
        $count = self::count() + 1;
        $number = str_pad($count, 4, '0', STR_PAD_LEFT);
        
        $kodeBooking = $prefix . $number;
        while (self::where('kode_booking', $kodeBooking)->exists()) {
            $count++;
            $number = str_pad($count, 4, '0', STR_PAD_LEFT);
            $kodeBooking = $prefix . $number;
        }
        
        return $kodeBooking;
    }
}
