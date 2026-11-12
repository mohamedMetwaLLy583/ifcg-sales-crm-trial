<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\VariationAttribute;
use Illuminate\Http\Request;

class ProductVariationController extends Controller
{
    public function index(Product $product)
    {
        $variations = $product->variations()
            ->with(['attributeValues.attribute', 'attributeValues.option'])
            ->latest()
            ->get();

        $attributes = VariationAttribute::where('is_active', true)
            ->with('activeOptions')
            ->orderBy('display_name')
            ->get();

        return view('dashboard.products.variations', compact('product', 'variations', 'attributes'));
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required_with:attributes', 'integer', 'exists:variation_attributes,id'],
            'attributes.*.option_id' => ['nullable', 'integer', 'exists:attribute_options,id'],
            'attributes.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        $variation = $product->variations()->create([
            'sku' => $data['sku'] ?? null,
            'price' => $data['price'] ?? $product->price,
            'sale_price' => $data['sale_price'] ?? null,
            'stock' => $data['stock'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        foreach ($data['attributes'] ?? [] as $attributeData) {
            $option = isset($attributeData['option_id'])
                ? AttributeOption::find($attributeData['option_id'])
                : null;
            $value = $attributeData['value'] ?? $option?->value ?? $option?->display_value;

            if (!$value) {
                continue;
            }

            $variation->attributeValues()->updateOrCreate(
                [
                    'variation_id' => $variation->id,
                    'attribute_id' => $attributeData['attribute_id'],
                ],
                [
                    'option_id' => $attributeData['option_id'] ?? null,
                    'value' => $value,
                ]
            );
        }

        if (!$product->has_variations) {
            $product->update(['has_variations' => true]);
        }

        return redirect()
            ->route('products.variations.index', $product)
            ->with('success', 'تم إنشاء متغير المنتج بنجاح');
    }

    public function update(Request $request, Product $product, ProductVariation $variation)
    {
        abort_unless((int) $variation->product_id === (int) $product->id, 404);

        $data = $request->validate([
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required_with:attributes', 'integer', 'exists:variation_attributes,id'],
            'attributes.*.option_id' => ['nullable', 'integer', 'exists:attribute_options,id'],
            'attributes.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        $variation->update([
            'sku' => $data['sku'] ?? null,
            'price' => $data['price'] ?? $product->price,
            'sale_price' => $data['sale_price'] ?? null,
            'stock' => $data['stock'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $variation->is_active,
        ]);

        foreach ($data['attributes'] ?? [] as $attributeData) {
            $option = isset($attributeData['option_id'])
                ? AttributeOption::find($attributeData['option_id'])
                : null;
            $value = $attributeData['value'] ?? $option?->value ?? $option?->display_value;

            if (!$value) {
                continue;
            }

            $variation->attributeValues()->updateOrCreate(
                [
                    'variation_id' => $variation->id,
                    'attribute_id' => $attributeData['attribute_id'],
                ],
                [
                    'option_id' => $attributeData['option_id'] ?? null,
                    'value' => $value,
                ]
            );
        }

        return redirect()
            ->route('products.variations.index', $product)
            ->with('success', 'تم تحديث متغير المنتج بنجاح');
    }

    public function destroy(Product $product, ProductVariation $variation)
    {
        abort_unless((int) $variation->product_id === (int) $product->id, 404);

        $variation->update(['is_active' => false]);

        return redirect()
            ->route('products.variations.index', $product)
            ->with('success', 'تم تعطيل متغير المنتج بنجاح');
    }
}
