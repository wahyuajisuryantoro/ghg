<?php

namespace App\Models\Api\Master;

use Illuminate\Database\Eloquent\Model;

class HotelImage extends Model
{
    protected $table = 'hotel_images';
    
    protected $fillable = [
        'idhotel',
        'fasilitas',
        'urlimage',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotels::class, 'idhotel');
    }

    public function getFullUrlAttribute()
    {
        return url($this->urlimage);
    }
}
