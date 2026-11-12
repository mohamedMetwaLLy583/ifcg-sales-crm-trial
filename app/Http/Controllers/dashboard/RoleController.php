<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_role')->only('index');
        $this->middleware('permission:create_role')->only('create', 'store');
        $this->middleware('permission:edit_role')->only('edit', 'update');
        $this->middleware('permission:delete_role')->only('destroy');
    }

    /**
     * عرض كل الرول ما عدا admin و customer
     */
    public function index()
    {
        $roles = Role::whereNotIn('name', ['admin', 'customer'])
            ->paginate(10);

        return view('dashboard.roles.index', compact('roles'));
    }

    /**
     * فورم إنشاء رول
     */
    public function create()
    {
        return view('dashboard.roles.create');
    }

    /**
     * حفظ رول جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        // إعطاء الصلاحيات
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'تم إنشاء الدور بنجاح');
    }


    /**
     * فورم تعديل رول
     */
    public function edit(Role $role)
    {
        // حماية إضافية
        if (in_array($role->name, ['admin', 'customer'])) {
            abort(403);
        }

        return view('dashboard.roles.edit', compact('role'));
    }

    /**
     * تحديث رول
     */
    public function update(Request $request, Role $role)
    {
        if (in_array($role->name, ['admin', 'customer'])) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        // تحديث الصلاحيات
        $role->syncPermissions($request->permissions ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'تم تحديث الدور بنجاح');
    }

    /**
     * عرض تفاصيل رول
     */

    public function show(Role $role)
    {
        // حماية إضافية للرولات الخاصة
        if (in_array($role->name, ['admin', 'customer'])) {
            abort(403);
        }

        return view('dashboard.roles.show', compact('role'));
    }

    /**
     * حذف رول
     */
    public function destroy(Role $role)
    {
        if (in_array($role->name, ['admin', 'customer'])) {
            abort(403);
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'تم حذف الدور بنجاح');
    }
}
