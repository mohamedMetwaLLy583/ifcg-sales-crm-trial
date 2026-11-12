<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\OrderActivityLogger;

class OrderObserver
{
    public function updated(Order $order): void
    {
        $logger = app(OrderActivityLogger::class);
        $notes = request()->input('admin_notes') ?: null;

        if ($order->wasChanged('status')) {
            $logger->log(
                $order,
                'status_changed',
                $order->getOriginal('status'),
                $order->status,
                $notes
            );
        }

        if ($order->wasChanged('payment_status')) {
            $logger->log(
                $order,
                'payment_status_changed',
                $order->getOriginal('payment_status'),
                $order->payment_status,
                $notes
            );
        }

        if ($order->wasChanged('admin_notes') && !$order->wasChanged('status') && !$order->wasChanged('payment_status')) {
            $logger->log($order, 'admin_notes_updated', null, null, $notes);
        }
    }
}
