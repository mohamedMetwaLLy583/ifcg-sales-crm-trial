<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variation_id',
        'quantity',
        'price',
        'subtotal',
        'status',
        'order_type',
        'rental_start_date',
        'rental_end_date',
        'rental_days',
        'deposit_amount',
        'rental_status',
        'rental_returned_at',
        'deposit_status',
        'deposit_refund_amount',
        'rental_notes',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'deposit_amount' => 'decimal:2',
        'rental_returned_at' => 'datetime',
        'deposit_refund_amount' => 'decimal:2',
    ];

    public const RENTAL_STATUSES = [
        'reserved' => 'محجوز',
        'picked_up' => 'تم التسليم للعميل',
        'returned' => 'تم الإرجاع',
        'overdue' => 'متأخر',
        'cancelled' => 'ملغي',
    ];

    public const DEPOSIT_STATUSES = [
        'none' => 'لا يوجد',
        'held' => 'محجوز',
        'refunded' => 'مسترد بالكامل',
        'partial' => 'مسترد جزئيا',
        'forfeited' => 'تم خصمه',
    ];

    public function getRentalStatusLabelAttribute(): string
    {
        return self::RENTAL_STATUSES[$this->rental_status] ?? ($this->rental_status ?: '-');
    }

    public function getDepositStatusLabelAttribute(): string
    {
        return self::DEPOSIT_STATUSES[$this->deposit_status] ?? ($this->deposit_status ?: '-');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }
}
