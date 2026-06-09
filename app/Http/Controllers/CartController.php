<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::with('product')
            ->where('user_id', auth()->id())
            ->get();
        $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
        return view('cart.index', compact('cartItems', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|not_in:0',
        ]);

        $product = Product::find($request->product_id);
        $delta = (int) $request->quantity;

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();
        $currentQty = $cartItem ? $cartItem->quantity : 0;
        $newQty = $currentQty + $delta;

        // Проверка доступности (если не unlimited)
        if (!$product->unlimited && $product->stock !== null) {
            if ($newQty > $product->stock) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Недостаточно товара на складе. Доступно: ' . $product->stock,
                    ], 422);
                }
                return back()->with('error', 'Недостаточно товара на складе.');
            }
        }

        if ($newQty > 0) {
            if ($cartItem) {
                $cartItem->update(['quantity' => $newQty]);
            } else {
                CartItem::create([
                    'user_id' => auth()->id(),
                    'product_id' => $product->id,
                    'quantity' => $delta,
                ]);
            }
        } else {
            if ($cartItem) $cartItem->delete();
        }

        $totalCount = CartItem::where('user_id', auth()->id())->count();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $delta > 0 ? 'Товар добавлен' : 'Количество обновлено',
                'cart_count' => $totalCount,
                'product_id' => $product->id,
                'item_quantity' => $newQty > 0 ? $newQty : 0,
            ]);
        }

        return back()->with('success', 'Изменения сохранены');
    }

    public function remove(CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) {
            abort(403);
        }
        $cartItem->delete();
        return back()->with('success', 'Товар удалён из корзины');
    }

    public function clear()
    {
        CartItem::where('user_id', auth()->id())->delete();
        return back()->with('success', 'Корзина очищена');
    }
}