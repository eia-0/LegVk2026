<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::withCount('products')->paginate(20);
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        Partner::create($request->only('full_name', 'phone', 'email'));
        return redirect()->route('admin.partners.index')->with('success', 'Партнёр добавлен');
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $partner->update($request->only('full_name', 'phone', 'email'));
        return redirect()->route('admin.partners.index')->with('success', 'Партнёр обновлён');
    }

    public function destroy(Partner $partner)
    {
        $partner->products()->update(['partner_id' => null]);
        $partner->delete();
        return redirect()->route('admin.partners.index')->with('success', 'Партнёр удалён');
    }

    public function show(Partner $partner)
    {
        $partner->load('products');
        $soldCount = $partner->soldItemsCount();
        return view('admin.partners.show', compact('partner', 'soldCount'));
    }
}