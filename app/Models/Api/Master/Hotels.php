<?php

namespace App\Models\Api\Master;

use Illuminate\Database\Eloquent\Model;

class Hotels extends Model
{
    protected $table = 'hotels';
    protected $fillable = [
        'hotelname',
        'idhotelcity',
        'hoteladdress',
        'notes',
        'bintang',
        'jarak',
        'hotellat',
        'hotellong',
    ];

    public function city()
    {
        return $this->belongsTo(Cities::class, 'idhotelcity');
    }
    public function images()
    {
        return $this->hasMany(HotelImage::class, 'idhotel');
    }
}
