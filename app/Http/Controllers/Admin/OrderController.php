<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        Order::where('admin_seen', false)->update(['admin_seen' => true]);
        $orders = Order::with('user', 'deliveryAddress')->latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if (!$order->admin_seen) {
            $order->update(['admin_seen' => true]);
        }
        $order->load('items.product', 'deliveryAddress', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validStatuses = ['new', 'accepted_cooking', 'ready_for_pickup', 'ready_for_delivery', 'delivering', 'completed', 'cancelled'];

        $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses),
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string|max:255',
        ], [
            'cancellation_reason.required_if' => 'Укажите причину отмены.',
        ]);

        // Возврат стока при отмене
        if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        }

        $order->status = $request->status;
        if ($request->status === 'cancelled') {
            $order->cancellation_reason = $request->cancellation_reason;
        } else {
            $order->cancellation_reason = null;
        }
        $order->save();

        return back()->with('success', 'Статус заказа обновлён');
    }

    public function unseenCount()
    {
        $count = Order::where('admin_seen', false)->count();
        return response()->json(['count' => $count]);
    }

    public function status(Order $order)
    {
        return response()->json([
            'status' => $order->status,
            'status_ru' => $order->status_ru,
            'status_color' => $order->status_color,
            'cancellation_reason' => $order->cancellation_reason,
        ]);
    }
}