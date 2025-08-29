<?php

namespace App\Models\Api\Master;

use App\Models\Api\Master\Country;
use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    protected $table = 'cities';

    protected $fillable = [
        'name',
        'countrycode',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'countrycode', 'code');
    }
}
