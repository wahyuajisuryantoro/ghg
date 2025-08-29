<?php

namespace App\Models\Api\Master;

use App\Models\Api\Booking\Jamaah;
use App\Models\Api\Booking\Booking;
use Illuminate\Database\Eloquent\Model;

class Packages extends Model
{
    protected $fillable = [
        'kode_paket',
        'tipe_paket_id',
        'total_kursi',
        'total_booked', 
        'booked_ppiu',    
        'booked_ghg',   
        'sisa_seat',    
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
        'hargabayi',
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'hargaber2' => 'decimal:2',
        'hargaber3' => 'decimal:2',
        'hargaber4' => 'decimal:2',
        'hargabayi' => 'decimal:2',
    ];

    public function packageType()
    {
        return $this->belongsTo(PackageType::class, 'tipe_paket_id');
    }

    public function airline()
    {
        return $this->belongsTo(Airlines::class, 'airlines_id', 'idairlines');
    }

    public function hotelMekka()
    {
        return $this->belongsTo(Hotels::class, 'hotel_mekka');
    }

    public function hotelMedina()
    {
        return $this->belongsTo(Hotels::class, 'hotel_medina');
    }

    public function hotelJedda()
    {
        return $this->belongsTo(Hotels::class, 'hotel_jedda');
    }

    public function kotaKeberangkatan()
    {
        return $this->belongsTo(KotaKeberangkatan::class, 'keberangkatan_id');
    }

    public function kotaTujuan()
    {
        return $this->belongsTo(KotaTujuan::class, 'kota_tujuan_id');
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'kode_paket', 'kode_paket');
    }

    public function jamaahs()
    {
        return $this->hasManyThrough(
            Jamaah::class,
            Booking::class,
            'kode_paket',
            'kode_booking',
            'kode_paket',
            'kode_booking'
        );
    }

    public function setTotalBookedAttribute($value)
    {
        $this->attributes['total_booked'] = $value;
        $this->attributes['sisa_seat'] = $this->attributes['total_kursi'] - $value;
    }

    public function getSisaKursiAttribute()
    {
        return $this->total_kursi - $this->total_booked;
    }

    public function bookSeats(int $jumlahKursi, string $source = 'ppiu'): bool
    {
        if ($this->sisa_kursi < $jumlahKursi) {
            return false;
        }

        $this->total_booked += $jumlahKursi;

        if ($source === 'ppiu') {
            $this->booked_ppiu += $jumlahKursi;
        } elseif ($source === 'ghg') {
            $this->booked_ghg += $jumlahKursi;
        }

        $this->sisa_seat = $this->total_kursi - $this->total_booked;

        return $this->save();
    }

    public function cancelBookedSeats(int $jumlahKursi, string $source = 'ppiu'): bool
    {
        if ($source === 'ppiu' && $this->booked_ppiu < $jumlahKursi) {
            return false;
        }

        if ($source === 'ghg' && $this->booked_ghg < $jumlahKursi) {
            return false;
        }

        $this->total_booked -= $jumlahKursi;

        if ($source === 'ppiu') {
            $this->booked_ppiu -= $jumlahKursi;
        } elseif ($source === 'ghg') {
            $this->booked_ghg -= $jumlahKursi;
        }

        $this->sisa_seat = $this->total_kursi - $this->total_booked;

        return $this->save();
    }

    public function scopeAvailable($query, int $jumlahKursi = 1)
    {
        return $query->whereRaw('(total_kursi - total_booked) >= ?', [$jumlahKursi]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($package) {
            $package->sisa_seat = $package->total_kursi;
        });

        static::updating(function ($package) {
            if ($package->isDirty('total_kursi') || $package->isDirty('total_booked')) {
                $package->sisa_seat = $package->total_kursi - $package->total_booked;
            }
        });
    }
}
