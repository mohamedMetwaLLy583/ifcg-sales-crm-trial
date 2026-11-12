<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    use HasFactory;

    public const STATUSES = [
        'requested',
        'approved',
        'rejected',
        'received',
        'refunded',
        'cancelled',
    ];

    public const STATUS_LABELS = [
        'requested' => 'طلب جديد',
        'approved' => 'مقبول',
        'rejected' => 'مرفوض',
        'received' => 'تم الاستلام',
        'refunded' => 'تم الاسترداد',
        'cancelled' => 'ملغي',
    ];

    protected $fillable = [
        'order_id',
        'order_item_id',
        'user_id',
        'status',
        'quantity',
        'refund_amount',
        'reason',
        'customer_note',
        'admin_note',
        'resolution',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return [
            'requested' => 'warning',
            'approved' => 'primary',
            'rejected' => 'danger',
            'received' => 'info',
            'refunded' => 'success',
            'cancelled' => 'secondary',
        ][$this->status] ?? 'secondary';
    }
}
