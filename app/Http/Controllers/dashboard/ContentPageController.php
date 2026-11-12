<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContentPageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContentPage::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $pages = $query
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => ContentPage::count(),
            'active' => ContentPage::where('is_active', true)->count(),
            'hidden' => ContentPage::where('is_active', false)->count(),
        ];

        return view('dashboard.pages.index', compact('pages', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePage($request);
        $validated['is_active'] = $request->boolean('is_active');

        ContentPage::create($validated);

        return back()->with('success', 'تم إنشاء الصفحة بنجاح.');
    }

    public function update(Request $request, ContentPage $page)
    {
        $validated = $this->validatePage($request, $page);
        $validated['is_active'] = $request->boolean('is_active');

        $page->update($validated);

        return back()->with('success', 'تم تحديث الصفحة بنجاح.');
    }

    public function destroy(ContentPage $page)
    {
        $page->update(['is_active' => false]);

        return back()->with('success', 'تم إخفاء الصفحة بنجاح.');
    }

    private function validatePage(Request $request, ?ContentPage $page = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'max:120', Rule::unique('content_pages', 'slug')->ignore($page?->id)],
            'title' => ['required', 'string', 'max:160'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
