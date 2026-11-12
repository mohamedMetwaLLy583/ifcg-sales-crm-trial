<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'old_price',
        'price',
        'discount',
        'quantity',
        'attributes',
        'is_active',
        'is_limited',
        'status',
        'has_variations'
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
        'is_limited' => 'boolean',
        'has_variations' => 'boolean',
        'old_price' => 'decimal:2',
        'price' => 'decimal:2'
    ];

    // العلاقات
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0)
            ->orWhereHas('variations', function ($q) {
                $q->where('stock', '>', 0);
            });
    }

    public function scopeWithDiscount($query)
    {
        return $query->whereHas('variations', function ($q) {
            $q->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'price');
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByCategorySlug($query, $slug)
    {
        return $query->whereHas('category', function ($q) use ($slug) {
            $q->where('slug', $slug)->where('is_active', true);
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhere('sku', 'like', "%{$search}%");
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    // Attribute Accessor للصورة الأساسية
    public function getPrimaryImageAttribute()
    {
        $image = $this->images()->where('is_primary', true)->first();
        if (!$image && $this->images()->exists()) {
            $image = $this->images()->first();
        }
        return $image ? asset('storage/' . $image->url) : null;
    }

    public function getImageUrlAttribute()
    {
        return $this->primary_image;
    }

    // الحصول على السعر بعد الخصم
    public function getDiscountedPriceAttribute()
    {
        if ($this->has_variations && $this->variations()->exists()) {
            return $this->variations()->min('sale_price') ?? $this->variations()->min('price');
        }
        return $this->price;
    }

    public function getDiscountPriceAttribute()
    {
        if ($this->has_variations && $this->variations()->whereNotNull('sale_price')->exists()) {
            return $this->variations()->min('sale_price');
        }

        if ($this->discount) {
            return round((float) $this->price * (100 - (int) $this->discount) / 100, 2);
        }

        return null;
    }

    public function getInStockAttribute()
    {
        if ($this->has_variations) {
            return $this->variations()->where('stock', '>', 0)->exists();
        }

        return $this->quantity > 0;
    }

    public function getRatingAttribute()
    {
        return round((float) $this->reviews()->where('approved', true)->avg('rating'), 1);
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->where('approved', true)->count();
    }

    // التحقق من وجود خصم
    public function getHasDiscountAttribute()
    {
        if ($this->has_variations) {
            return $this->variations()->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'price')
                ->exists();
        }
        return false;
    }
}
