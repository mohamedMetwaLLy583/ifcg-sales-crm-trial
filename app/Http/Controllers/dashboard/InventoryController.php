<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $productsQuery = Product::query()
            ->with(['category', 'images', 'variations.attributeValues.attribute', 'variations.attributeValues.option'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $productsQuery->where('category_id', $request->integer('category_id'));
        }

        match ($request->input('stock_status')) {
            'out' => $productsQuery->where('quantity', '<=', 0),
            'low' => $productsQuery->where('quantity', '>', 0)->where('quantity', '<=', 5),
            'ok' => $productsQuery->where('quantity', '>', 5),
            default => null,
        };

        $products = $productsQuery
            ->orderBy('quantity')
            ->latest()
            ->paginate(25)
            ->appends($request->query());

        $productOptions = Product::query()
            ->where('is_active', true)
            ->with(['variations.attributeValues.attribute', 'variations.attributeValues.option'])
            ->orderBy('name')
            ->get();

        $latestMovements = InventoryMovement::query()
            ->with(['product', 'variation.attributeValues.attribute', 'variation.attributeValues.option', 'user'])
            ->latest()
            ->limit(12)
            ->get();

        $stats = [
            'products' => Product::where('is_active', true)->count(),
            'out' => Product::where('is_active', true)->where('quantity', '<=', 0)->count(),
            'low' => Product::where('is_active', true)->where('quantity', '>', 0)->where('quantity', '<=', 5)->count(),
            'movements_today' => InventoryMovement::whereDate('created_at', today())->count(),
        ];

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.inventory.index', compact(
            'products',
            'productOptions',
            'latestMovements',
            'stats',
            'categories'
        ));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variation_id' => ['nullable', 'integer', 'exists:product_variations,id'],
            'type' => ['required', 'in:increase,decrease,set'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($validated['type'], ['increase', 'decrease'], true) && (int) $validated['quantity'] < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'الكمية يجب أن تكون 1 على الأقل عند الزيادة أو الخصم.',
            ]);
        }

        DB::transaction(function () use ($validated) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($validated['product_id']);

            $variation = null;
            if (!empty($validated['product_variation_id'])) {
                $variation = ProductVariation::query()
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->find($validated['product_variation_id']);

                if (!$variation) {
                    throw ValidationException::withMessages([
                        'product_variation_id' => 'المتغير المختار لا يتبع هذا المنتج.',
                    ]);
                }
            }

            $target = $variation ?: $product;
            $stockColumn = $variation ? 'stock' : 'quantity';
            $before = (int) ($target->{$stockColumn} ?? 0);
            $quantity = (int) $validated['quantity'];

            $after = match ($validated['type']) {
                'increase' => $before + $quantity,
                'decrease' => $before - $quantity,
                'set' => $quantity,
            };

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'لا يمكن أن يصبح المخزون أقل من صفر.',
                ]);
            }

            $target->update([$stockColumn => $after]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'product_variation_id' => $variation?->id,
                'user_id' => auth()->id(),
                'type' => $validated['type'],
                'quantity_before' => $before,
                'quantity_change' => $after - $before,
                'quantity_after' => $after,
                'reason' => $validated['reason'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);
        });

        return back()->with('success', 'تم تحديث المخزون وتسجيل الحركة بنجاح.');
    }
}
