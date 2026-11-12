<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_product')->only('index');
        $this->middleware('permission:create_product')->only(['create', 'store']);
        $this->middleware('permission:edit_product')->only(['edit', 'update', 'setPrimaryImage', 'destroyImage']);
        $this->middleware('permission:delete_product')->only('destroy');
    }

    public function index()
    {
        $query = Product::with(['category', 'images']);

        if (request()->filled('search')) {
            $query->search(request('search'));
        }

        if (request()->filled('status')) {
            $query->where('is_active', request('status'));
        }

        if (request()->filled('stock')) {
            match (request('stock')) {
                'in' => $query->where('quantity', '>', 0),
                'out' => $query->where('quantity', '<=', 0),
                'low' => $query->where('is_active', true)->where('quantity', '<=', 5),
                default => null,
            };
        }

        if (request()->filled('service')) {
            $query->where('attributes->service', request('service'));
        }

        $products = $query->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.products.index', compact('products'));
    }

    public function create()
    {
        return view('dashboard.products.form', [
            'product' => new Product(['is_active' => true, 'status' => 'new', 'quantity' => 0]),
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data = $this->prepareProductData($request, $data);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['has_variations'] = false;
        $data['is_limited'] = $request->boolean('is_limited');

        $product = Product::create($data);
        $this->storeImages($request, $product);

        return redirect()->route('products.index')->with('success', 'تم إنشاء المنتج بنجاح');
    }

    public function edit(Product $product)
    {
        $product->load('images');

        return view('dashboard.products.form', [
            'product' => $product,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validatedData($request, $product);
        $data = $this->prepareProductData($request, $data, $product);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_limited'] = $request->boolean('is_limited');

        $product->update($data);
        $this->storeImages($request, $product);

        return redirect()->route('products.index')->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);

        return redirect()->route('products.index')->with('success', 'تم تعطيل المنتج بنجاح');
    }

    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return redirect()->route('products.edit', $product)->with('success', 'تم تحديد الصورة الرئيسية');
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('public')->delete($image->url);
        $wasPrimary = $image->is_primary;
        $image->delete();

        if ($wasPrimary && $product->images()->exists()) {
            $product->images()->oldest()->first()->update(['is_primary' => true]);
        }

        return redirect()->route('products.edit', $product)->with('success', 'تم حذف الصورة');
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        $productId = $product?->id;

        return $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_limited' => ['nullable', 'boolean'],
            'status' => ['required', 'in:new,old'],
            'service' => ['required', 'in:sale,rental,both'],
            'rental_price' => ['nullable', 'numeric', 'min:0'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'rental_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'color' => ['nullable', 'string', 'max:100'],
            'fit' => ['nullable', 'string', 'max:100'],
            'material' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'sizes' => ['nullable', 'string', 'max:255'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    private function prepareProductData(Request $request, array $data, ?Product $product = null): array
    {
        unset($data['images']);

        $attributes = (array) ($product?->attributes ?? []);
        $attributes['service'] = $data['service'];
        $attributes['rental_price'] = $data['rental_price'] ?? null;
        $attributes['deposit'] = $data['deposit'] ?? null;
        $attributes['rental_days'] = $data['rental_days'] ?? null;
        $attributes['color'] = $data['color'] ?? null;
        $attributes['material'] = $data['material'] ?? ($attributes['material'] ?? null);
        $attributes['dimensions'] = $data['dimensions'] ?? ($attributes['dimensions'] ?? null);
        $attributes['fit'] = $data['fit'] ?? null;
        $attributes['sizes'] = collect(explode(',', (string) ($data['sizes'] ?? '')))
            ->map(fn ($size) => trim($size))
            ->filter()
            ->values()
            ->all();

        foreach (['service', 'rental_price', 'deposit', 'rental_days', 'color', 'fit', 'sizes', 'material', 'dimensions'] as $field) {
            unset($data[$field]);
        }

        $data['attributes'] = array_filter($attributes, fn ($value) => $value !== null && $value !== '' && $value !== []);

        return $data;
    }

    private function storeImages(Request $request, Product $product): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('products/' . $product->id, 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'url' => $path,
                'is_primary' => !$product->images()->exists() && $index === 0,
            ]);
        }
    }
}
