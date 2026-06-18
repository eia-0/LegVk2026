@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Пользователи</h1>

    {{-- Поиск --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск по имени или email"
               class="border rounded px-3 py-1.5 w-64">
        <button type="submit" class="bg-amber-500 text-white px-4 py-1.5 rounded">Поиск</button>
    </form>

    {{-- Сообщение (в том числе новый пароль после сброса) --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Имя</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Роль</th>
                    <th class="p-3 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-t">
                    <td class="p-3">{{ $user->name }}</td>
                    <td class="p-3">{{ $user->email }}</td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold text-white
                            {{ $user->role === 'courier' ? 'bg-blue-500' : ($user->is_admin ? 'bg-amber-500' : 'bg-gray-400') }}">
                            {{ $user->role === 'courier' ? 'Курьер' : ($user->is_admin ? 'Админ' : 'Пользователь') }}
                        </span>
                    </td>
                    <td class="p-3">
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Смена роли --}}
                            <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="role" class="border rounded px-2 py-1 text-sm">
                                    <option value="user" {{ $user->role === 'user' && !$user->is_admin ? 'selected' : '' }}>Пользователь</option>
                                    <option value="courier" {{ $user->role === 'courier' ? 'selected' : '' }}>Курьер</option>
                                </select>
                                <button type="submit" class="bg-amber-500 text-white px-3 py-1 rounded text-sm">Сохранить</button>
                            </form>

                            {{-- Сброс пароля --}}
                            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                                  onsubmit="return confirm('Сгенерировать новый пароль для {{ $user->name }}?')">
                                @csrf
                                <button type="submit" class="text-amber-600 hover:underline text-sm">
                                    Сбросить пароль
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">
            {{ $users->links() }}
        </div>
    </div>
@endsection