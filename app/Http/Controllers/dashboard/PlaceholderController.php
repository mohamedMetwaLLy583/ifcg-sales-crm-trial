<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlaceholderController extends Controller
{
    public function show(Request $request)
    {
        return view('dashboard.placeholder', [
            'title' => $request->route('title'),
            'description' => $request->route('description'),
            'icon' => $request->route('icon', 'ti ti-apps'),
        ]);
    }
}
