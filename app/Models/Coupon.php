<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    public const TYPES = ['fixed', 'percent'];

    public const TYPE_LABELS = [
        'fixed' => 'قيمة ثابتة',
        'percent' => 'نسبة مئوية',
    ];

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'minimum_order_total',
        'max_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_total' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'معطل';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'مجدول';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'منتهي';
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'مكتمل الاستخدام';
        }

        return 'نشط';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_label) {
            'نشط' => 'success',
            'مجدول' => 'info',
            'منتهي', 'مكتمل الاستخدام' => 'warning',
            'معطل' => 'secondary',
            default => 'secondary',
        };
    }

    public function isUsableFor(float $itemsTotal): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return $itemsTotal >= (float) $this->minimum_order_total;
    }

    public function discountFor(float $itemsTotal): float
    {
        if (!$this->isUsableFor($itemsTotal)) {
            return 0.0;
        }

        $discount = $this->type === 'percent'
            ? $itemsTotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $itemsTotal), 2);
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}
