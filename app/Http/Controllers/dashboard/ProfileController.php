<?php

namespace App\Http\Controllers\dashboard;

use App\Models\User;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    // profile edit page
    public function edit()
    {
        $user = auth()->user();
        return view('dashboard.profile.edit', compact('user'));
    }

    // update profile
    public function update()
    {
        $user = auth()->user();

        $data = request()->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|max:255|unique:users,phone,' . $user->id,
        ]);

        $user->update($data);

        // redirect with toast message
        return redirect()->back()->with('success', 'تم تحديث البيانات بنجاح!');
    }

}
