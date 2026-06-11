@extends('layouts.app')

@section('title', 'Профиль курьера')

@section('content')
    <div class="max-w-lg mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Мой профиль</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <form action="{{ route('courier.profile.update') }}" method="POST" class="bg-white rounded-2xl shadow p-6">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Номер телефона</label>
                <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                       class="w-full border rounded-lg px-4 py-2 focus:ring-amber-500 focus:border-amber-500"
                       placeholder="+7 (___) ___-__-__">
            </div>

            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-6 rounded-full transition">
                Сохранить
            </button>
        </form>
    </div>
@endsection