<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'display_value',
        'color_code',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function attribute()
    {
        return $this->belongsTo(VariationAttribute::class, 'attribute_id');
    }

    public function attributeValues()
    {
        return $this->hasMany(VariationAttributeValue::class, 'option_id');
    }
}