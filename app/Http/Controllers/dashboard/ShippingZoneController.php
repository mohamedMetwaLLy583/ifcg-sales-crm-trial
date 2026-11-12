<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingZoneController extends Controller
{
    public function index()
    {
        $zones = ShippingZone::query()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.shipping.index', compact('zones'));
    }

    public function create()
    {
        return view('dashboard.shipping.form', [
            'zone' => new ShippingZone(['is_active' => true, 'shipping_cost' => 0]),
        ]);
    }

    public function store(Request $request)
    {
        ShippingZone::create($this->validatedData($request));

        return redirect()->route('shipping.index')->with('success', 'تم إنشاء منطقة الشحن بنجاح');
    }

    public function edit(ShippingZone $zone)
    {
        return view('dashboard.shipping.form', compact('zone'));
    }

    public function update(Request $request, ShippingZone $zone)
    {
        $zone->update($this->validatedData($request));

        return redirect()->route('shipping.index')->with('success', 'تم تحديث منطقة الشحن بنجاح');
    }

    public function destroy(ShippingZone $zone)
    {
        $zone->update(['is_active' => false]);

        return redirect()->route('shipping.index')->with('success', 'تم تعطيل منطقة الشحن بنجاح');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'cities' => ['required', 'string'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['cities'] = collect(explode(',', $data['cities']))
            ->map(fn (string $city) => trim($city))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
