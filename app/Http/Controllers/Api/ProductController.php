<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * عرض جميع المنتجات مع فلترة متقدمة
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['category', 'images'])
                ->where('is_active', true)
                ->orderBy('created_at', 'desc');

            // فلترة حسب التصنيف
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // فلترة حسب التصنيف الفرعي (جميع أبناء التصنيف)
            if ($request->has('category_slug')) {
                $category = \App\Models\Category::where('slug', $request->category_slug)
                    ->where('is_active', true)
                    ->first();

                if ($category) {
                    // الحصول على جميع IDs للأبناء
                    $categoryIds = $this->getAllCategoryChildrenIds($category);
                    $categoryIds[] = $category->id;

                    $query->whereIn('category_id', $categoryIds);
                }
            }

            // البحث في الاسم والوصف
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            // فلترة حسب السعر
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // فلترة حسب المخزون
            if ($request->has('in_stock')) {
                $query->where('quantity', '>', 0);
            }

            // فلترة حسب وجود التباينات
            if ($request->has('has_variations')) {
                $query->where('has_variations', $request->boolean('has_variations'));
            }

            // التحميل الإضافي للعلاقات
            if ($request->boolean('with_variations')) {
                $query->with(['variations' => function ($q) {
                    $q->where('is_active', true)
                        ->with(['attributeValues.attribute', 'attributeValues.option']);
                }]);
            }

            // ترتيب النتائج
            if ($request->has('sort_by')) {
                $sortBy = $request->sort_by;
                $sortDirection = $request->get('sort_direction', 'asc');

                $allowedSorts = ['name', 'price', 'created_at', 'quantity'];

                if (in_array($sortBy, $allowedSorts)) {
                    $query->orderBy($sortBy, $sortDirection);
                }
            }

            // الباجينيش
            $perPage = $request->get('per_page', 15);
            $products = $query->paginate($perPage);

            // معالجة البيانات لتكون مناسبة للتطبيق
            $products->getCollection()->transform(function ($product) {
                // إذا كان المنتج له تباينات، نجهز البيانات
                if ($product->has_variations && $product->relationLoaded('variations')) {
                    $product->min_price = $product->variations->min('price');
                    $product->max_price = $product->variations->max('price');
                    $product->has_discount = $product->variations->whereNotNull('sale_price')->count() > 0;
                } else {
                    $product->min_price = $product->price;
                    $product->max_price = $product->price;
                    $product->has_discount = false;
                }

                return $product;
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب المنتجات بنجاح',
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'filters' => $request->all()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المنتجات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض منتج واحد بالتفاصيل
     */
    public function show(Request $request, $idOrSlug): JsonResponse
    {
        try {
            $query = Product::with(['category', 'images'])
                ->where('is_active', true);

            // البحث باستخدام ID أو Slug
            if (is_numeric($idOrSlug)) {
                $query->where('id', $idOrSlug);
            } else {
                $query->where('slug', $idOrSlug);
            }

            // تحميل التباينات وسماتها
            if ($request->boolean('with_variations')) {
                $query->with(['variations' => function ($q) {
                    $q->where('is_active', true)
                        ->with(['attributeValues.attribute', 'attributeValues.option']);
                }]);
            }

            $product = $query->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير موجود'
                ], 404);
            }

            // تحميل المنتجات المرتبطة (من نفس التصنيف)
            $relatedProducts = Product::with(['category', 'images'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();

            // معالجة البيانات للمنتجات ذات التباينات
            if ($product->has_variations && $product->relationLoaded('variations') && $product->variations->isNotEmpty()) {
                $product->min_price = $product->variations->min('price');
                $product->max_price = $product->variations->max('price');
                $product->total_stock = $product->variations->sum('stock');
                $product->has_discount = $product->variations->whereNotNull('sale_price')->count() > 0;

            } else {
                $product->min_price = $product->price;
                $product->max_price = $product->price;
                $product->total_stock = $product->quantity;
                $product->has_discount = false;
            }

            return response()->json([
                'success' => true,
                'message' => 'تم جلب المنتج بنجاح',
                'data' => new ProductResource($product),
                'related_products' => ProductResource::collection($relatedProducts)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المنتج',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * البحث المتقدم في المنتجات
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['category', 'images'])
                ->where('is_active', true);

            // البحث في النص
            if ($request->has('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            }

            // فلترة حسب الفئة
            if ($request->has('category')) {
                $category = $request->input('category');
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category)
                        ->orWhere('name', 'like', "%{$category}%");
                });
            }

            // فلترة حسب السعر
            if ($request->has('price_range')) {
                $priceRange = explode('-', $request->input('price_range', ''));
                if (count($priceRange) === 2) {
                    $query->whereBetween('price', [
                        floatval($priceRange[0]),
                        floatval($priceRange[1])
                    ]);
                }
            }

            // فلترة حسب السمات (للمنتجات ذات التباينات)
            if ($request->has('attributes')) {
                $attributes = json_decode($request->input('attributes', '{}'), true);
                if (is_array($attributes) && !empty($attributes)) {
                    $query->whereHas('variations.attributeValues', function ($q) use ($attributes) {
                        foreach ($attributes as $attributeId => $optionId) {
                            $q->where('attribute_id', intval($attributeId))
                                ->where('option_id', intval($optionId));
                        }
                    });
                }
            }

            // الترتيب
            $sortBy = $request->input('sort', 'relevance');
            switch ($sortBy) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('quantity', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $perPage = $request->input('per_page', 20);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'تم البحث بنجاح',
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'search_query' => $request->input('q', ''),
                    'filters_applied' => $request->except(['q', 'page', 'per_page'])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء البحث',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على سمات متاحة للمنتج (للمنتجات ذات التباينات)
     */
    private function getAvailableAttributes($product): array
    {
        if (!$product->has_variations || !$product->relationLoaded('variations')) {
            return [];
        }

        $attributes = [];

        foreach ($product->variations as $variation) {
            foreach ($variation->attributeValues as $attributeValue) {
                $attributeId = $attributeValue->attribute_id;
                $optionId = $attributeValue->option_id;

                if (!isset($attributes[$attributeId])) {
                    $attributes[$attributeId] = [
                        'id' => $attributeId,
                        'name' => $attributeValue->attribute->name,
                        'display_name' => $attributeValue->attribute->display_name,
                        'input_type' => $attributeValue->attribute->input_type,
                        'options' => []
                    ];
                }

                // إضافة الخيار إذا لم يكن مضافاً
                $optionExists = collect($attributes[$attributeId]['options'])
                    ->contains('id', $optionId);

                if (!$optionExists && $attributeValue->option) {
                    $attributes[$attributeId]['options'][] = [
                        'id' => $optionId,
                        'value' => $attributeValue->option->value,
                        'display_value' => $attributeValue->option->display_value,
                        'color_code' => $attributeValue->option->color_code
                    ];
                }
            }
        }

        return array_values($attributes);
    }

    /**
     * الحصول على مصفوفة التباينات
     */
    private function getVariationMatrix($product): array
    {
        if (!$product->has_variations || !$product->relationLoaded('variations')) {
            return [];
        }

        $matrix = [];

        foreach ($product->variations as $variation) {
            $attributes = [];
            foreach ($variation->attributeValues as $attributeValue) {
                $attributes[$attributeValue->attribute_id] = $attributeValue->option_id;
            }

            ksort($attributes);
            $key = md5(json_encode($attributes));

            $matrix[$key] = [
                'variation_id' => $variation->id,
                'attributes' => $attributes,
                'price' => $variation->price,
                'sale_price' => $variation->sale_price,
                'stock' => $variation->stock,
                'sku' => $variation->sku,
                'image' => $variation->image
            ];
        }

        return $matrix;
    }

    /**
     * الحصول على مصفوفة سمات التباين
     */
    private function getVariationAttributesArray($variation): array
    {
        $attributes = [];

        foreach ($variation->attributeValues as $attributeValue) {
            $attributes[] = [
                'attribute_id' => $attributeValue->attribute_id,
                'attribute_name' => $attributeValue->attribute->display_name,
                'option_id' => $attributeValue->option_id,
                'option_value' => $attributeValue->option->display_value,
                'color_code' => $attributeValue->option->color_code
            ];
        }

        return $attributes;
    }

    /**
     * الحصول على جميع IDs لأبناء التصنيف
     */
    private function getAllCategoryChildrenIds($category): array
    {
        $ids = [];

        $getChildrenIds = function ($category) use (&$ids, &$getChildrenIds) {
            foreach ($category->children as $child) {
                if ($child->is_active) {
                    $ids[] = $child->id;
                    $getChildrenIds($child);
                }
            }
        };

        $getChildrenIds($category->load('children'));

        return $ids;
    }
}
