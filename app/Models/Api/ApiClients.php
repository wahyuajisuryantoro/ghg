<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class ApiClients extends Model
{
    protected $table = 'api_clients';
    protected $fillable = [
        'client_id',
        'client_key',
        'password_basic_auth',
        'client_name',
        'is_active'
    ];

    protected $hidden = [
        'client_key',
        'password_basic_auth'
    ];
}
