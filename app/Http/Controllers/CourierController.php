<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ShopSetting;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index()
    {
        $courier = auth()->user();
        if (!$courier->isCourier() && !$courier->isAdmin()) {
            abort(403);
        }

        // Доступные заказы (статусы new, accepted_cooking, ready_for_delivery, без назначенного курьера)
        $orders = Order::with('items.product', 'deliveryAddress', 'user')
            ->whereIn('status', ['new', 'accepted_cooking', 'ready_for_delivery'])
            ->whereNull('courier_id')
            ->latest()
            ->get();

        // Активные заказы ЭТОГО курьера (delivering и completed)
        $myOrders = Order::with('items.product', 'deliveryAddress', 'user')
            ->where('courier_id', $courier->id)
            ->whereIn('status', ['delivering', 'completed'])
            ->latest()
            ->get();

        $settings = ShopSetting::getSettings();

        return view('courier.index', compact('orders', 'myOrders', 'courier', 'settings'));
    }

    public function accept(Order $order)
    {
        $courier = auth()->user();

        if ($order->status !== 'ready_for_delivery') {
            return back()->with('error', 'Этот заказ ещё не готов к доставке');
        }

        if ($order->courier_id) {
            return back()->with('error', 'Заказ уже взят другим курьером');
        }

        $order->update([
            'courier_id' => $courier->id,
            'status'     => 'delivering',
        ]);

        return back()->with('success', 'Вы взяли заказ №' . $order->id);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $courier = auth()->user();

        if ($order->courier_id !== $courier->id) {
            return back()->with('error', 'Это не ваш заказ');
        }

        $request->validate([
            'status' => 'required|in:delivering,completed',
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Статус заказа №' . $order->id . ' обновлён');
    }
}