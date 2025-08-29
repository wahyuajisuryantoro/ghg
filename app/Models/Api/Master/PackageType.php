<?php

namespace App\Models\Api\Master;

use Illuminate\Database\Eloquent\Model;

class PackageType extends Model
{
    protected $table = 'package_types';
    protected $fillable = [
        'category',
        'name',
    ];
}
