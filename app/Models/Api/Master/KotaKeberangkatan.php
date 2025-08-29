<?php

namespace App\Models\Api\Master;

use Illuminate\Database\Eloquent\Model;

class KotaKeberangkatan extends Model
{
    protected $table = 'kota_keberangkatan';
    protected $fillable = [
        'name',
    ];
}
