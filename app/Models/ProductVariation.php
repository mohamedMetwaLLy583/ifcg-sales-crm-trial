<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'stock',
        'image',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(VariationAttributeValue::class, 'variation_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(
            VariationAttribute::class,
            'variation_attribute_values',
            'variation_id',
            'attribute_id'
        )->withPivot('value')->withTimestamps();
    }

    // الحصول على قيمة سمة معينة
    public function getVariationAttributeValue($attributeName)
    {
        $attributeValue = $this->attributeValues()
            ->whereHas('attribute', function ($query) use ($attributeName) {
                $query->where('name', $attributeName);
            })
            ->first();

        return $attributeValue ? $attributeValue->value : null;
    }

    // إعداد قيمة سمة
    public function setVariationAttributeValue($attributeName, $value, $optionId = null)
    {
        $attribute = VariationAttribute::firstOrCreate(
            ['name' => $attributeName],
            [
                'display_name' => ucfirst($attributeName),
                'is_active' => true,
                'input_type' => 'select'
            ]
        );

        VariationAttributeValue::updateOrCreate(
            [
                'variation_id' => $this->id,
                'attribute_id' => $attribute->id
            ],
            [
                'value' => $value,
                'option_id' => $optionId
            ]
        );
    }

    // الحصول على جميع السمات كـ array
    public function getAttributesArray()
    {
        return $this->attributeValues->mapWithKeys(function ($item) {
            return [
                $item->attribute->name => [
                    'value' => $item->value,
                    'display_value' => $item->option ? $item->option->display_value : $item->value,
                    'option_id' => $item->option_id,
                    'attribute_id' => $item->attribute_id,
                    'color_code' => $item->option ? $item->option->color_code : null
                ]
            ];
        })->toArray();
    }
}
