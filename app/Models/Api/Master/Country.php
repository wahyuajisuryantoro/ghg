<?php

namespace App\Models\Api\Master;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'country';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
    ];

    public function cities()
    {
        return $this->hasMany(Cities::class, 'countrycode', 'code');
    }

    public function hotels()
    {
        return $this->hasManyThrough(Hotels::class, Cities::class, 'countrycode', 'idhotelcity', 'code', 'id');
    }
}
