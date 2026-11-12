<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('placement', 'like', "%{$search}%");
            });
        }

        if ($request->filled('placement')) {
            $query->where('placement', $request->input('placement'));
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active' => $query->where('is_active', true),
                'disabled' => $query->where('is_active', false),
                default => null,
            };
        }

        $banners = $query
            ->orderBy('placement')
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Banner::count(),
            'published' => Banner::published()->count(),
            'home_hero' => Banner::where('placement', 'home_hero')->count(),
            'disabled' => Banner::where('is_active', false)->count(),
        ];

        return view('dashboard.banners.index', compact('banners', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateBanner($request);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image_path'] = $this->storeImage($request);

        Banner::create($validated);

        return back()->with('success', 'تم إنشاء البنر بنجاح.');
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $this->validateBanner($request);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $newImage = $this->storeImage($request);

            if ($newImage) {
                if ($banner->image_path) {
                    Storage::disk('public')->delete($banner->image_path);
                }

                $validated['image_path'] = $newImage;
            }
        }

        $banner->update($validated);

        return back()->with('success', 'تم تحديث البنر بنجاح.');
    }

    public function destroy(Banner $banner)
    {
        $banner->update(['is_active' => false]);

        return back()->with('success', 'تم تعطيل البنر بنجاح.');
    }

    private function validateBanner(Request $request): array
    {
        return $request->validate([
            'placement' => ['required', 'string', 'max:60', Rule::in(['home_hero', 'home_strip', 'shop_top'])],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:80'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('banners', 'public');
    }
}
