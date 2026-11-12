<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::query()->with(['user', 'product']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('approved', $request->input('status') === 'approved');
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        $reviews = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('approved', false)->count(),
            'approved' => Review::where('approved', true)->count(),
            'average' => round((float) Review::where('approved', true)->avg('rating'), 1),
        ];

        return view('dashboard.reviews.index', compact('reviews', 'stats'));
    }

    public function approve(Review $review)
    {
        $review->update(['approved' => true]);

        return back()->with('success', 'تم اعتماد التقييم بنجاح.');
    }

    public function hide(Review $review)
    {
        $review->update(['approved' => false]);

        return back()->with('success', 'تم إخفاء التقييم بنجاح.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'تم حذف التقييم بنجاح.');
    }
}
