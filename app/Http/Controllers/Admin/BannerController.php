<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'interval' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        $data = [
            'interval' => $request->interval ?? 0,
            'order' => $request->order ?? 0,
            'active' => $request->has('active'),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        Banner::create($data);
        return redirect()->route('admin.banners.index')->with('success', 'Баннер добавлен');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'interval' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        $banner->interval = $request->interval ?? 0;
        $banner->order = $request->order ?? 0;
        $banner->active = $request->has('active');

        if ($request->hasFile('image')) {
            if ($banner->image) {
                \Storage::disk('public')->delete($banner->image);
            }
            $banner->image = $request->file('image')->store('banners', 'public');
        }

        $banner->save();
        return redirect()->route('admin.banners.index')->with('success', 'Баннер обновлён');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            \Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();
        return redirect()->route('admin.banners.index')->with('success', 'Баннер удалён');
    }
}