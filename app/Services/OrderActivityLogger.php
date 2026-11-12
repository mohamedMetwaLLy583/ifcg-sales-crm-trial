<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderActivity;

class OrderActivityLogger
{
    public function log(Order $order, string $action, ?string $from, ?string $to, ?string $notes = null): OrderActivity
    {
        return OrderActivity::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'from_value' => $from,
            'to_value' => $to,
            'notes' => $notes,
            'ip_address' => request()?->ip(),
        ]);
    }
}
