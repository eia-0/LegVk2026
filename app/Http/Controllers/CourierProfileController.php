<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourierProfileController extends Controller
{
    public function edit()
    {
        return view('courier.profile');
    }

    public function update(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
        ]);

        auth()->user()->update(['phone' => $request->phone]);

        return back()->with('success', 'Номер телефона сохранён');
    }
}