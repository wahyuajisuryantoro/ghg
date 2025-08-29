<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class ApiTokens extends Model
{
    protected $table = 'api_tokens';

    protected $fillable = [
        'token',
        'expired',
    ];

    public $timestamps = true;
}
