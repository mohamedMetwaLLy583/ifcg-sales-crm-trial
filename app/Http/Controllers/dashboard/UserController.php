<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_user')->only('index');
        $this->middleware('permission:create_user')->only('create', 'store');
        $this->middleware('permission:edit_user')->only('edit', 'update');
        $this->middleware('permission:delete_user')->only('destroy');
    }

    public function index()
    {
        $users = User::whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['admin', 'customer']);
        })
            ->paginate(10);
        return view('dashboard.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereNotIn('name', ['admin', 'customer'])->get();
        return view('dashboard.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->is_active,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    public function edit(User $user)
    {
        $roles = Role::whereNotIn('name', ['admin', 'customer'])->get();
        return view('dashboard.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->is_active,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function toggle(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->back()->with('success', $user->is_active ? 'تم تفعيل المستخدم' : 'تم إلغاء تفعيل المستخدم');
    }

    public function show(User $user)
    {
        // حماية إضافية، لا يسمح بعرض admin أو customer
        if ($user->hasAnyRole(['admin', 'customer'])) {
            abort(403);
        }

        return view('dashboard.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        // حماية إضافية، لا يسمح بحذف admin أو customer
        if ($user->hasAnyRole(['admin', 'customer'])) {
            abort(403);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح');
    }
}
