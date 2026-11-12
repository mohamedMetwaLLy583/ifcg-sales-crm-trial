<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_category')->only('index');
        $this->middleware('permission:create_category')->only(['create', 'store']);
        $this->middleware('permission:edit_category')->only(['edit', 'update']);
        $this->middleware('permission:delete_category')->only('destroy');
    }

    public function index()
    {
        $query = Category::with('parent')->withCount('products');

        if (request()->filled('search')) {
            $query->search(request('search'));
        }

        if (request()->filled('status')) {
            $query->where('is_active', request('status'));
        }

        $categories = $query->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('dashboard.categories.form', [
            'category' => new Category(['is_active' => true]),
            'parents' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $this->prepareImage($request, $data);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        Category::create($data);

        return redirect()->route('dashboard.categories.index')->with('success', 'تم إنشاء التصنيف بنجاح');
    }

    public function edit(Category $category)
    {
        return view('dashboard.categories.form', [
            'category' => $category,
            'parents' => $this->availableParents($category),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validatedData($request, $category);
        $this->prepareImage($request, $data, $category);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $category->update($data);

        return redirect()->route('dashboard.categories.index')->with('success', 'تم تحديث التصنيف بنجاح');
    }

    public function destroy(Category $category)
    {
        $category->update(['is_active' => false]);

        return redirect()->route('dashboard.categories.index')->with('success', 'تم تعطيل التصنيف بنجاح');
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function prepareImage(Request $request, array &$data, ?Category $category = null): void
    {
        if (!$request->hasFile('image')) {
            unset($data['image']);
            return;
        }

        if ($category?->image) {
            Storage::disk('public')->delete($category->image);
        }

        $data['image'] = $request->file('image')->store('categories', 'public');
    }

    private function availableParents(Category $category)
    {
        $blockedIds = array_merge([$category->id], $this->descendantIds($category));

        return Category::active()
            ->whereNotIn('id', $blockedIds)
            ->orderBy('name')
            ->get();
    }

    private function descendantIds(Category $category): array
    {
        $ids = [];
        $category->loadMissing('children');

        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->descendantIds($child));
        }

        return $ids;
    }
}
