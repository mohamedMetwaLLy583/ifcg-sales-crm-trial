<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VariationAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'display_name', 'input_type', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function values()
    {
        return $this->hasMany(VariationAttributeValue::class, 'attribute_id');
    }

    public function options()
    {
        return $this->hasMany(AttributeOption::class, 'attribute_id')->where('is_active', true)->orderBy('sort_order');
    }

    public function activeOptions()
    {
        return $this->hasMany(AttributeOption::class, 'attribute_id')->where('is_active', true)->orderBy('sort_order');
    }

    public function variations()
    {
        return $this->belongsToMany(
            ProductVariation::class,
            'variation_attribute_values',
            'attribute_id',
            'variation_id'
        )->withPivot('value')->withTimestamps();
    }
}