<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * عرض جميع التصنيفات مع إمكانية الفلترة
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Category::query()->withCount('children');

            // فلترة حسب النشاط
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            } else {
                $query->where('is_active', true);
            }

            // فلترة حسب التصنيف الرئيسي
            if ($request->filled('parent_id')) {
                $request->parent_id === 'null'
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $request->parent_id);
            }

            // البحث (آمن)
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%");
                });
            }

            // تحميل العلاقات بحذر
            $query->when($request->boolean('with_parent'), function ($q) {
                $q->with('parent:id,name');
            });

            $query->when($request->boolean('with_children'), function ($q) {
                $q->with(['children' => function ($q) {
                    $q->select('id', 'parent_id', 'name')->withCount('children');
                }]);
            });

            $query->when($request->boolean('with_products'), function ($q) {
                $q->with(['products' => function ($q) {
                    $q->select('id', 'category_id', 'name', 'price')
                        ->limit(20); // حماية
                }]);
            });

            // الترتيب (آمن)
            $orderBy = in_array($request->order_by, ['name', 'created_at'])
                ? $request->order_by
                : 'name';

            $orderDirection = $request->order_direction === 'desc' ? 'desc' : 'asc';

            $query->orderBy($orderBy, $orderDirection);

            // Pagination محمي
            $perPage = min((int)$request->get('per_page', 15), 50);
            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'تم جلب التصنيفات بنجاح',
                'data' => CategoryResource::collection($categories),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Categories API Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب التصنيفات'
            ], 500);
        }
    }


    /**
     * عرض تصنيف واحد
     */
    public function show(Request $request, Category $category): JsonResponse
    {
        try {
            if (!$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'التصنيف غير موجود'
                ], 404);
            }

            // تحميل العلاقات المطلوبة
            $with = [];
            if ($request->has('with_parent')) {
                $with[] = 'parent';
            }
            if ($request->has('with_children')) {
                $with[] = 'children';
            }
            if ($request->has('with_products')) {
                $with[] = 'products';
            }

            if (!empty($with)) {
                $category->load($with);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم جلب التصنيف بنجاح',
                'data' => new CategoryResource($category)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب التصنيف',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * الحصول على جميع التصنيفات الرئيسية
     */
    public function rootCategories(): JsonResponse
    {
        try {
            $categories = Category::root()
                ->active()
                ->withCount('children')
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'تم جلب التصنيفات الرئيسية بنجاح',
                'data' => CategoryResource::collection($categories)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب التصنيفات الرئيسية',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على شجرة التصنيفات
     */
    public function tree(): JsonResponse
    {
        try {
            $categories = Category::with(['children' => function ($query) {
                $query->withCount('children')->ordered();
            }])
                ->root()
                ->active()
                ->withCount('children')
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'تم جلب شجرة التصنيفات بنجاح',
                'data' => CategoryResource::collection($categories)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب شجرة التصنيفات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على جميع أبناء التصنيف (بشكل متكرر)
     */
    public function allChildren(Category $category): JsonResponse
    {
        try {
            $category->load(['children' => function ($query) {
                $query->withCount('children')->with('allChildren');
            }]);

            return response()->json([
                'success' => true,
                'message' => 'تم جلب جميع الأبناء بنجاح',
                'data' => new CategoryResource($category)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأبناء',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
}
