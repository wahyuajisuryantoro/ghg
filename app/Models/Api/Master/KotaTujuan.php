<?php

namespace App\Models\Api\Master;

use Illuminate\Database\Eloquent\Model;

class KotaTujuan extends Model
{
    protected $table = 'kota_tujuan';
    protected $fillable = [
        'name',
    ];
}
