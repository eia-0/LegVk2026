<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $cartQuantity = 0;
        if (auth()->check()) {
            $cartItem = \App\Models\CartItem::where('user_id', auth()->id())
                ->where('product_id', $product->id)->first();
            $cartQuantity = $cartItem ? $cartItem->quantity : 0;
        }
        return view('product.show', compact('product', 'cartQuantity'));
    }

    public function index()
    {
        $products = Product::with('category')->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::with('children')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'category_id' => 'required|exists:categories,id',
            'preparation_time' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
            'unlimited' => 'boolean',
        ]);

        $data = $request->only('name', 'description', 'price', 'category_id', 'preparation_time', 'stock', 'unlimited');
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        $data['unlimited'] = $request->has('unlimited');
        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Товар добавлен');
    }

    public function edit(Product $product)
    {
        $categories = Category::with('children')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'category_id' => 'required|exists:categories,id',
            'preparation_time' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
            'unlimited' => 'boolean',
        ]);

        $data = $request->only('name', 'description', 'price', 'category_id', 'preparation_time', 'stock', 'unlimited');
        if ($request->hasFile('image')) {
            if ($product->image) {
                \Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        $data['unlimited'] = $request->has('unlimited');
        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Товар обновлен');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            \Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Товар удален');
    }
}