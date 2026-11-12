<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Payment extends Model
{
    protected $fillable = ['order_id', 'amount', 'provider', 'transaction_id', 'status', 'provider_data'];
    protected $casts = ['provider_data' => 'array'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
