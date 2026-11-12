<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FavoriteProduct;
use App\Models\FavoriteCategory;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    public function addFavoriteProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer|exists:products,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'خطأ في التحقق',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $productId = $request->product_id;

            // التحقق إذا كان المنتج موجود بالفعل في المفضلة
            $existingFavorite = FavoriteProduct::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($existingFavorite) {
                return response()->json([
                    'status' => false,
                    'message' => 'المنتج موجود بالفعل في المفضلة',
                    'is_favorite' => true
                ], 400);
            }

            // إضافة المنتج إلى المفضلة
            $favorite = FavoriteProduct::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);

            // جلب تفاصيل المنتج
            $product = Product::find($productId);

            return response()->json([
                'status' => true,
                'message' => 'تم إضافة المنتج إلى المفضلة بنجاح',
                'data' => [
                    'favorite' => [
                        'id' => $favorite->id,
                        'product' => [
                            'id' => $product->id,
                            'name' => $product->name,
                            'image' => $product->image_url ?? null,
                            'price' => $product->price,
                            'discount_price' => $product->discount_price,
                            'in_stock' => $product->in_stock
                        ],
                        'added_at' => $favorite->created_at
                    ],
                    'total_favorites' => Auth::user()->favoriteProducts()->count()
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إضافة المنتج إلى المفضلة',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function removeFavoriteProduct(Request $request, $productId = null)
    {
        try {
            // إذا تم إرسال product_id في body
            if ($request->has('product_id')) {
                $productId = $request->product_id;
            }

            if (!$productId) {
                return response()->json([
                    'status' => false,
                    'message' => 'معرف المنتج مطلوب'
                ], 400);
            }

            $userId = Auth::id();

            // البحث عن المنتج في المفضلة
            $favorite = FavoriteProduct::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'status' => false,
                    'message' => 'المنتج غير موجود في المفضلة',
                    'is_favorite' => false
                ], 404);
            }

            // حذف المنتج من المفضلة
            $product = $favorite->product;
            $favorite->delete();

            return response()->json([
                'status' => true,
                'message' => 'تم حذف المنتج من المفضلة بنجاح',
                'data' => [
                    'removed_product' => [
                        'id' => $product->id,
                        'name' => $product->name
                    ],
                    'total_favorites' => Auth::user()->favoriteProducts()->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء حذف المنتج من المفضلة',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getFavoriteProducts(Request $request)
    {
        try {
            $user = Auth::user();

            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            // جلب المنتجات المفضلة مع تفاصيل المنتج
            $favorites = $user->favoriteProductDetails()
                ->with(['category', 'images', 'variations'])
                ->orderBy('favorite_products.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // تنسيق الاستجابة
            $formattedFavorites = $favorites->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'final_price' => $product->discount_price ?? $product->price,
                    'in_stock' => $product->in_stock,
                    'rating' => $product->rating,
                    'review_count' => $product->review_count,
                    'image' => $product->images->first()->url ?? null,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug
                    ] : null,
                    'added_at' => $product->pivot->created_at,
                    'variations_count' => $product->variations->count()
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'المنتجات المفضلة',
                'data' => [
                    'favorites' => $formattedFavorites,
                    'pagination' => [
                        'current_page' => $favorites->currentPage(),
                        'per_page' => $favorites->perPage(),
                        'total' => $favorites->total(),
                        'total_pages' => $favorites->lastPage(),
                        'has_more_pages' => $favorites->hasMorePages()
                    ],
                    'meta' => [
                        'total_favorites' => $favorites->total(),
                        'user_id' => $user->id
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب المنتجات المفضلة',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function addFavoriteCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'خطأ في التحقق',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $categoryId = $request->category_id;

            // التحقق إذا كان التصنيف موجود بالفعل في المفضلة
            $existingFavorite = FavoriteCategory::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->first();

            if ($existingFavorite) {
                return response()->json([
                    'status' => false,
                    'message' => 'التصنيف موجود بالفعل في المفضلة',
                    'is_favorite' => true
                ], 400);
            }

            // إضافة التصنيف إلى المفضلة
            $favorite = FavoriteCategory::create([
                'user_id' => $userId,
                'category_id' => $categoryId
            ]);

            // جلب تفاصيل التصنيف
            $category = Category::with('parent')->find($categoryId);

            return response()->json([
                'status' => true,
                'message' => 'تم إضافة التصنيف إلى المفضلة بنجاح',
                'data' => [
                    'favorite' => [
                        'id' => $favorite->id,
                        'category' => [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                            'description' => $category->description,
                            'image' => $category->image_url ?? null,
                            'parent' => $category->parent ? [
                                'id' => $category->parent->id,
                                'name' => $category->parent->name
                            ] : null
                        ],
                        'added_at' => $favorite->created_at
                    ],
                    'total_favorite_categories' => Auth::user()->favoriteCategories()->count()
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إضافة التصنيف إلى المفضلة',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function removeFavoriteCategory(Request $request, $categoryId = null)
    {
        try {
            // إذا تم إرسال category_id في body
            if ($request->has('category_id')) {
                $categoryId = $request->category_id;
            }

            if (!$categoryId) {
                return response()->json([
                    'status' => false,
                    'message' => 'معرف التصنيف مطلوب'
                ], 400);
            }

            $userId = Auth::id();

            // البحث عن التصنيف في المفضلة
            $favorite = FavoriteCategory::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'status' => false,
                    'message' => 'التصنيف غير موجود في المفضلة',
                    'is_favorite' => false
                ], 404);
            }

            // حذف التصنيف من المفضلة
            $category = $favorite->category;
            $favorite->delete();

            return response()->json([
                'status' => true,
                'message' => 'تم حذف التصنيف من المفضلة بنجاح',
                'data' => [
                    'removed_category' => [
                        'id' => $category->id,
                        'name' => $category->name
                    ],
                    'total_favorite_categories' => Auth::user()->favoriteCategories()->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء حذف التصنيف من المفضلة',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getFavoriteCategories(Request $request)
    {
        try {
            $user = Auth::user();

            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            // جلب التصنيفات المفضلة مع تفاصيل التصنيف
            $favorites = $user->favoriteCategoryDetails()
                ->with(['parent', 'children'])
                ->orderBy('favorite_categories.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // تنسيق الاستجابة
            $formattedFavorites = $favorites->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image_url ?? null,
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name
                    ] : null,
                    'children_count' => $category->children->count(),
                    'products_count' => $category->products_count ?? 0,
                    'added_at' => $category->pivot->created_at
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'التصنيفات المفضلة',
                'data' => [
                    'favorites' => $formattedFavorites,
                    'pagination' => [
                        'current_page' => $favorites->currentPage(),
                        'per_page' => $favorites->perPage(),
                        'total' => $favorites->total(),
                        'total_pages' => $favorites->lastPage(),
                        'has_more_pages' => $favorites->hasMorePages()
                    ],
                    'meta' => [
                        'total_favorite_categories' => $favorites->total(),
                        'user_id' => $user->id
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب التصنيفات المفضلة',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

}
