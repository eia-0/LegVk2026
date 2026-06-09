<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DeliveryAddress;
use App\Models\ShopSetting;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function create()
    {
        $cartItems = CartItem::with('product')->where('user_id', auth()->id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        $settings = ShopSetting::getSettings();
        $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        if ($settings->min_order_amount !== null && $total < $settings->min_order_amount) {
            return redirect()->route('cart.index')->with('error', 'Минимальная сумма заказа: ' . $settings->min_order_amount . ' ₽');
        }

        $addresses = DeliveryAddress::where('user_id', auth()->id())->get();

        $deliveryCost = 0;
        if ($settings->delivery_enabled) {
            if ($settings->free_delivery_from && $total >= $settings->free_delivery_from) {
                $deliveryCost = 0;
            } else {
                $deliveryCost = $settings->delivery_cost ?? 0;
            }
        }

        $maxPrepTime = $cartItems->max(fn($item) => $item->product->preparation_time ?? 0);
        $minDeliveryDate = now()->format('Y-m-d');
        $minDeliveryTime = '00:00';

        if ($maxPrepTime > 0 || $settings->opening_hours) {
            $readyAt = now()->addMinutes($maxPrepTime + 30);
            
            // Извлекаем время открытия и закрытия из настройки (формат "ЧЧ:ММ-ЧЧ:ММ")
            $openTime = '12:00';
            $closeTime = '23:00';
            if ($settings->opening_hours && preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $settings->opening_hours, $matches)) {
                $openTime = $matches[1];
                $closeTime = $matches[2];
            }

            $now = now();
            $open = Carbon::parse($now->format('Y-m-d') . ' ' . $openTime);
            $close = Carbon::parse($now->format('Y-m-d') . ' ' . $closeTime);

            // Если текущее время позже закрытия, работаем со следующим днём
            if ($now->gt($close)) {
                $open->addDay();
                $close->addDay();
            }

            if ($readyAt->gt($close)) {
                // После закрытия: переносим на следующий день к открытию + 2 часа
                $readyAt = $open->copy()->addDay()->addHours(2);
            } elseif ($readyAt->lt($open)) {
                // До открытия: переносим на открытие + 2 часа
                $readyAt = $open->copy()->addHours(2);
            }

            $minDeliveryDate = $readyAt->format('Y-m-d');
            $minDeliveryTime = $readyAt->format('H:i');
        }

        // Для самовывоза также показываем минимальное время, если оно позже текущего
        $pickupReadyAt = null;
        if ($maxPrepTime > 0) {
            $pickupReadyAt = now()->addMinutes($maxPrepTime + 30);
            if ($settings->opening_hours) {
                $openTime = '12:00';
                $closeTime = '23:00';
                if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $settings->opening_hours, $matches)) {
                    $openTime = $matches[1];
                    $closeTime = $matches[2];
                }
                $now = now();
                $open = Carbon::parse($now->format('Y-m-d') . ' ' . $openTime);
                $close = Carbon::parse($now->format('Y-m-d') . ' ' . $closeTime);
                if ($now->gt($close)) {
                    $open->addDay();
                    $close->addDay();
                }
                if ($pickupReadyAt->gt($close)) {
                    $pickupReadyAt = $open->copy()->addDay()->addHours(2);
                } elseif ($pickupReadyAt->lt($open)) {
                    $pickupReadyAt = $open->copy()->addHours(2);
                }
            }
        }

        return view('order.create', compact(
            'cartItems', 'total', 'addresses', 'settings', 'deliveryCost',
            'maxPrepTime', 'minDeliveryDate', 'minDeliveryTime', 'pickupReadyAt'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_address_id' => 'nullable',
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/'],
            'callback_needed' => 'boolean',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'delivery_time' => 'nullable|date_format:H:i',
        ], [
            'phone.regex' => 'Введите номер в формате +7 (999) 999-99-99',
        ]);

        $cartItems = CartItem::with('product')->where('user_id', auth()->id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
        $settings = ShopSetting::getSettings();

        if ($settings->min_order_amount !== null && $subtotal < $settings->min_order_amount) {
            return redirect()->route('cart.index')->with('error', 'Минимальная сумма заказа: ' . $settings->min_order_amount . ' ₽');
        }

        // Проверка адреса доставки
        $hasNewAddress = false;
        if ($request->delivery_type === 'delivery') {
            $hasExistingAddress = $request->filled('delivery_address_id') && $request->delivery_address_id !== 'new';
            $hasNewAddress = $request->has('new_address.latitude') && $request->has('new_address.longitude')
                             && !empty($request->input('new_address.latitude')) && !empty($request->input('new_address.longitude'));

            if (!$hasExistingAddress && !$hasNewAddress) {
                return back()->withErrors(['delivery_address_id' => 'Необходимо выбрать или добавить адрес доставки'])->withInput();
            }
        }

        // Проверка времени доставки
        if ($request->delivery_type === 'delivery' && $request->filled('delivery_date') && $request->filled('delivery_time')) {
            $maxPrep = $cartItems->max(fn($item) => $item->product->preparation_time ?? 0);
            $minDateTime = Carbon::now()->addMinutes($maxPrep + 30);
            $selectedDateTime = Carbon::parse($request->delivery_date . ' ' . $request->delivery_time);
            if ($selectedDateTime < $minDateTime) {
                return back()->withErrors([
                    'delivery_time' => 'Минимальное время доставки: ' . $minDateTime->format('d.m.Y H:i')
                ])->withInput();
            }
        }

        $deliveryAddressId = null;
        if ($request->delivery_type === 'delivery') {
            if ($request->filled('delivery_address_id') && $request->delivery_address_id !== 'new') {
                $address = DeliveryAddress::where('id', $request->delivery_address_id)
                    ->where('user_id', auth()->id())->first();
                if ($address) {
                    $deliveryAddressId = $address->id;
                } else {
                    return back()->withErrors(['delivery_address_id' => 'Выбранный адрес не найден'])->withInput();
                }
            } elseif ($hasNewAddress) {
                $address = DeliveryAddress::create([
                    'user_id' => auth()->id(),
                    'street' => $request->input('new_address.street', ''),
                    'house' => $request->input('new_address.house', ''),
                    'entrance' => $request->input('new_address.entrance', ''),
                    'apartment' => $request->input('new_address.apartment', ''),
                    'intercom' => $request->input('new_address.intercom', ''),
                    'latitude' => $request->input('new_address.latitude'),
                    'longitude' => $request->input('new_address.longitude'),
                ]);
                $deliveryAddressId = $address->id;
            }
        }

        foreach ($cartItems as $item) {
            if (!$item->product->isAvailable($item->quantity)) {
                return redirect()->route('cart.index')->with('error', 
                    'Товар "' . $item->product->name . '" недоступен в нужном количестве.');
            }
        }

        $deliveryCost = 0;
        if ($request->delivery_type === 'delivery' && $settings->delivery_enabled) {
            if ($settings->free_delivery_from && $subtotal >= $settings->free_delivery_from) {
                $deliveryCost = 0;
            } else {
                $deliveryCost = $settings->delivery_cost ?? 0;
            }
        }
        $total = $subtotal + $deliveryCost;

        $order = Order::create([
            'user_id' => auth()->id(),
            'delivery_type' => $request->delivery_type,
            'delivery_address_id' => $deliveryAddressId,
            'phone' => $request->phone,
            'callback_needed' => $request->boolean('callback_needed'),
            'delivery_date' => $request->delivery_date,
            'delivery_time' => $request->delivery_time,
            'status' => 'new',
            'total' => $total,
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
            $item->product->decrementStock($item->quantity);
        }

        CartItem::where('user_id', auth()->id())->delete();

        return redirect()->route('orders.show', $order)->with('success', 'Заказ оформлен');
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }
        $order->load('items.product', 'deliveryAddress');
        return view('order.show', compact('order'));
    }

    public function index()
    {
        $orders = Order::where('user_id', auth()->id())->latest()->paginate(10);
        return view('order.index', compact('orders'));
    }

    public function status(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        return response()->json([
            'status' => $order->status,
            'status_ru' => $order->status_ru,
            'status_color' => $order->status_color,
            'cancellation_reason' => $order->cancellation_reason,
        ]);
    }
}