<?php

namespace App\Models\Api\Master;

use Illuminate\Database\Eloquent\Model;

class Airlines extends Model
{
    protected $table = 'airlines';
    protected $primaryKey = 'idairlines';
    
    protected $fillable = [
        'airlinesname'
    ];
}
