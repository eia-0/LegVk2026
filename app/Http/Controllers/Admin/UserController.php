<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,courier',
        ]);

        $user->role = $request->role;
        $user->save();

        return back()->with('success', 'Роль пользователя обновлена');
    }

    /**
     * Сброс пароля – генерируется новый читаемый пароль и показывается администратору.
     */
    public function resetPassword(User $user)
    {
        $password = Str::random(8); // 8 символов, только буквы/цифры

        $user->update([
            'password' => Hash::make($password),
        ]);

        return back()->with('success', "Новый пароль для {$user->name}: {$password}");
    }
}