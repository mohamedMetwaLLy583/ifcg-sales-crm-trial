<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'is_active' => $this->is_active,
            'has_variations' => $this->has_variations,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // العلاقات
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug
                ];
            }),

            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => asset('storage/' . $image->url),
                        'is_primary' => $image->is_primary
                    ];
                });
            }),

            'primary_image' => $this->whenLoaded('images', function () {
                $primary = $this->images->where('is_primary', true)->first();
                if (!$primary && $this->images->isNotEmpty()) {
                    $primary = $this->images->first();
                }
                return $primary ? asset('storage/' . $primary->url) : null;
            }),
        ];

        // إضافة بيانات التباينات إذا تم تحميلها
        if ($this->relationLoaded('variations')) {
            $data['variations'] = $this->variations->map(function ($variation) {
                return [
                    'id' => $variation->id,
                    'sku' => $variation->sku,
                    'price' => $variation->price,
                    'sale_price' => $variation->sale_price,
                    'stock' => $variation->stock,
                    'is_active' => $variation->is_active,
                    'attributes' => $variation->relationLoaded('attributeValues')
                        ? $variation->attributeValues->map(function ($attrValue) {
                            return [
                                'attribute_name' => $attrValue->attribute?->display_name ?? $attrValue->attribute?->name,
                                'option_value' => $attrValue->option?->display_value
                                    ?? $attrValue->option?->value
                                    ?? $attrValue->value,
                                'color_code' => $attrValue->option?->color_code ?? null
                            ];
                        })
                        : []
                ];
            });

            // إضافة أسعار النطاق
            if (isset($this->min_price)) {
                $data['price_range'] = [
                    'min' => $this->min_price,
                    'max' => $this->max_price
                ];
            }

            // إضافة حالة الخصم
            if (isset($this->has_discount)) {
                $data['has_discount'] = $this->has_discount;
            }
        } else {
            // للمنتجات بدون تباينات
            $data['price'] = $this->price;
            $data['stock'] = $this->quantity;
        }

        // إضافة البيانات المحسوبة إذا كانت موجودة
        if (isset($this->total_stock)) {
            $data['total_stock'] = $this->total_stock;
        }

        return $data;
    }
}
