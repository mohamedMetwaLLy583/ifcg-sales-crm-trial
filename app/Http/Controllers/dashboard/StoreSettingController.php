<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class StoreSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $settings = StoreSetting::query()
            ->pluck('value', 'key')
            ->toArray();

        return view('dashboard.settings.store', [
            'settings' => $settings,
            'currencies' => ['EGP', 'USD', 'EUR'],
        ]);
    }

    public function checkout()
    {
        return view('dashboard.settings.checkout', [
            'settings' => StoreSetting::query()->pluck('value', 'key')->toArray(),
        ]);
    }

    public function updateCheckout(Request $request)
    {
        $validated = $request->validate([
            'vodafone_cash_number' => ['nullable', 'string', 'max:30'],
            'instapay_address' => ['nullable', 'string', 'max:100'],
        ]);

        StoreSetting::set('vodafone_cash_number', trim((string) ($validated['vodafone_cash_number'] ?? '')));
        StoreSetting::set('instapay_address', trim((string) ($validated['instapay_address'] ?? '')));

        return redirect()
            ->route('dashboard.settings.checkout')
            ->with('success', 'تم حفظ بيانات الدفع بنجاح.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:100'],
            'store_whatsapp' => ['nullable', 'string', 'max:20'],
            'store_email' => ['nullable', 'email', 'max:100'],
            'store_address' => ['nullable', 'string', 'max:255'],
            'store_currency' => ['required', 'in:EGP,USD,EUR'],
            'store_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ]);

        foreach (['store_name', 'store_whatsapp', 'store_email', 'store_address', 'store_currency'] as $key) {
            StoreSetting::set($key, $validated[$key] ?? '');
        }

        if ($request->hasFile('store_logo')) {
            StoreSetting::set('store_logo_path', $request->file('store_logo')->store('store', 'public'));
        }

        return redirect()
            ->route('dashboard.settings.store')
            ->with('success', 'تم حفظ إعدادات المتجر بنجاح');
    }
}
