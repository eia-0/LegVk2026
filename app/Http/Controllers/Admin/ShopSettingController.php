<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopSetting;
use Illuminate\Http\Request;

class ShopSettingController extends Controller
{
    public function edit()
    {
        $settings = ShopSetting::getSettings();
        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'delivery_enabled' => 'boolean',
            'delivery_cost' => 'numeric|min:0',
            'free_delivery_from' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'opening_hours' => 'required|string|max:1000',
            'pickup_address' => 'nullable|string|max:500',
            'pickup_entrance' => 'nullable|string|max:50',
            'pickup_latitude' => 'nullable|numeric',
            'pickup_longitude' => 'nullable|numeric',
            'phone' => 'nullable|string|max:30',
        ]);

        $settings = ShopSetting::getSettings();
        $settings->update($request->only([
            'delivery_enabled', 'delivery_cost', 'free_delivery_from',
            'min_order_amount', 'opening_hours', 'pickup_address',
            'pickup_entrance', 'pickup_latitude', 'pickup_longitude',
            'phone',
        ]));

        // Сбрасываем кэш после обновления настроек
        cache()->forget('shop_settings');

        return back()->with('success', 'Настройки обновлены');
    }
}