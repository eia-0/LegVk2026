<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'show_in_catalog' => 'boolean',
            'show_in_ready_eat' => 'boolean',
        ]);

        $data = $request->only('name', 'parent_id', 'show_in_catalog', 'show_in_ready_eat');
        $data['show_in_catalog'] = $request->has('show_in_catalog');
        $data['show_in_ready_eat'] = $request->has('show_in_ready_eat');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        Category::create($data);

        cache()->forget('home_categories_food');
        cache()->forget('home_categories_products');

        return redirect()->route('admin.categories.index')->with('success', 'Категория создана');
    }

    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'show_in_catalog' => 'boolean',
            'show_in_ready_eat' => 'boolean',
        ]);

        $data = $request->only('name', 'parent_id', 'show_in_catalog', 'show_in_ready_eat');
        $data['show_in_catalog'] = $request->has('show_in_catalog');
        $data['show_in_ready_eat'] = $request->has('show_in_ready_eat');

        if ($request->hasFile('image')) {
            if ($category->image) {
                \Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        $category->update($data);

        cache()->forget('home_categories_food');
        cache()->forget('home_categories_products');

        return redirect()->route('admin.categories.index')->with('success', 'Категория обновлена');
    }

    public function destroy(Category $category)
    {
        if ($category->image) {
            \Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        cache()->forget('home_categories_food');
        cache()->forget('home_categories_products');

        return redirect()->route('admin.categories.index')->with('success', 'Категория удалена');
    }
}