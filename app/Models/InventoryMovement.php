<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variation_id',
        'user_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reason',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'increase' => 'زيادة',
            'decrease' => 'خصم',
            'set' => 'تصحيح',
            default => $this->type,
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'increase' => 'success',
            'decrease' => 'danger',
            'set' => 'primary',
            default => 'secondary',
        };
    }
}
