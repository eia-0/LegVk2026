<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Characteristic;
use Illuminate\Http\Request;

class CharacteristicController extends Controller
{
    public function index()
    {
        $characteristics = Characteristic::orderBy('order')->get();
        return view('admin.characteristics.index', compact('characteristics'));
    }

    public function create()
    {
        return view('admin.characteristics.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:10',
            'order' => 'nullable|integer',
        ]);

        Characteristic::create($request->only('name', 'color', 'icon', 'order'));
        return redirect()->route('admin.characteristics.index')->with('success', 'Характеристика добавлена');
    }

    public function edit(Characteristic $characteristic)
    {
        return view('admin.characteristics.edit', compact('characteristic'));
    }

    public function update(Request $request, Characteristic $characteristic)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:10',
            'order' => 'nullable|integer',
        ]);

        $characteristic->update($request->only('name', 'color', 'icon', 'order'));
        return redirect()->route('admin.characteristics.index')->with('success', 'Характеристика обновлена');
    }

    public function destroy(Characteristic $characteristic)
    {
        $characteristic->delete();
        return redirect()->route('admin.characteristics.index')->with('success', 'Характеристика удалена');
    }
}