<?php

namespace App\Http\Controllers\dashboard;

use App\Models\User;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_customer')->only('index');
        $this->middleware('permission:edit_customer')->only('activate', 'deactivate');
        $this->middleware('permission:delete_customer')->only('destroy');
    }

    public function index()
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'customer');
        });

        // 🔍 البحث بالاسم أو الإيميل
        if (request()->filled('search')) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('email', 'like', '%' . request('search') . '%');
            });
        }

        // 🟢 الحالة
        if (request()->filled('status')) {
            $query->where('is_active', request('status'));
        }

        // 📅 تاريخ التسجيل
        if (request()->filled('date')) {
            $query->whereDate('created_at', request('date'));
        }

        $customers = $query->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString(); // مهم جدًا مع pagination

        return view('dashboard.customers.index', compact('customers'));
    }

    public function activate(User $user)
    {
        $user->update(['is_active' => 1]);

        return redirect()->back()->with('success', 'تم تفعيل العميل بنجاح!');

    }

    public function deactivate(User $user)
    {
        $user->update(['is_active' => 0]);

        return redirect()->back()->with('success', 'تم إلغاء تفعيل العميل');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', 'تم حذف العميل');
    }
}
