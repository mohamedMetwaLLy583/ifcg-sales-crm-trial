<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Order extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_address',
        'shipping_city',
        'customer_notes',
        'admin_notes',
        'total',
        'shipping_total',
        'tax_total',
        'status',
        'payment_status',
        'payment_method',
        'coupon_id',
        'coupon_code',
        'discount_total'
    ];

    public const STATUSES = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];
    public const PAYMENT_METHODS = ['cash_on_delivery', 'whatsapp', 'vodafone_cash', 'instapay'];

    public const STATUS_LABELS = [
        'pending' => 'جديد',
        'confirmed' => 'مؤكد',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغي',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid' => 'غير مدفوع',
        'paid' => 'مدفوع',
        'refunded' => 'مسترد',
    ];

    public const PAYMENT_METHOD_LABELS = [
        'cash_on_delivery' => 'الدفع عند الاستلام',
        'whatsapp' => 'واتساب',
        'vodafone_cash' => 'فودافون كاش',
        'instapay' => 'إنستاباي',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function activities()
    {
        return $this->hasMany(OrderActivity::class);
    }

    public function getCustomerDisplayNameAttribute()
    {
        return $this->customer_name ?: $this->user?->name;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute()
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? $this->payment_status;
    }

    public function getPaymentMethodLabelAttribute()
    {
        return self::PAYMENT_METHOD_LABELS[$this->payment_method] ?? $this->payment_method;
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'pending' => 'warning',
            'confirmed' => 'primary',
            'shipped' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
        ][$this->status] ?? 'secondary';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        return [
            'unpaid' => 'secondary',
            'paid' => 'success',
            'refunded' => 'info',
        ][$this->payment_status] ?? 'secondary';
    }

    public function getWhatsappUrlAttribute()
    {
        if (!$this->customer_phone) {
            return null;
        }

        $phone = preg_replace('/\D+/', '', $this->customer_phone);
        $message = "مرحبًا {$this->customer_display_name}، بخصوص طلبك رقم {$this->order_number}.";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }
}
