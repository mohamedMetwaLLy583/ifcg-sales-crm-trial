<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\OboorMarketPriceImporter;
use Illuminate\Http\Request;
use Throwable;

class MarketPriceController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('sku', 'like', 'OBOOR-%')
            ->whereHas('category', fn ($q) => $q->where('slug', 'fruits'))
            ->with(['category', 'images']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $categorySlug = trim((string) $request->input('category'));
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $products = $query
            ->latest('updated_at')
            ->paginate(30)
            ->appends($request->query());

        $baseQuery = Product::query()
            ->where('sku', 'like', 'OBOOR-%')
            ->whereHas('category', fn ($q) => $q->where('slug', 'fruits'));
        $latestProduct = (clone $baseQuery)->latest('updated_at')->first();
        $latestAttributes = (array) ($latestProduct?->attributes ?? []);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'fruits' => (clone $baseQuery)->whereHas('category', fn ($q) => $q->where('slug', 'fruits'))->count(),
            'images' => ProductImage::query()
                ->whereHas('product', fn ($q) => $q->where('sku', 'like', 'OBOOR-%')->whereHas('category', fn ($category) => $category->where('slug', 'fruits')))
                ->count(),
            'source_date' => $latestAttributes['source_date'] ?? null,
            'last_imported_at' => $latestAttributes['market_imported_at'] ?? $latestProduct?->updated_at?->format('Y-m-d H:i'),
        ];

        $categories = Category::query()
            ->whereIn('slug', ['fruits'])
            ->orderBy('name')
            ->get();

        return view('dashboard.market-prices.index', compact('products', 'stats', 'categories'));
    }

    public function import(Request $request, OboorMarketPriceImporter $importer)
    {
        $validated = $request->validate([
            'download_images' => ['nullable', 'boolean'],
        ]);

        try {
            $summary = $importer->import((bool) ($validated['download_images'] ?? false));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'تعذر تحديث أسعار سوق العبور الآن. راجع الاتصال أو سجل الأخطاء.');
        }

        return back()->with(
            'success',
            'تم تحديث الأسعار بنجاح: '
            . number_format($summary['total'])
            . ' صنف، جديد: '
            . number_format($summary['created'])
            . '، محدث: '
            . number_format($summary['updated'])
            . '.'
        );
    }
}
