<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = ['amount', 'fee', 'status', 'provider_response'];
    protected $casts = ['provider_response' => 'array'];

    
}
