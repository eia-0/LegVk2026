@extends('layouts.app')

@section('title', 'Вход')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-3xl font-bold text-center text-amber-700 mb-6">Вход</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 transition">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 transition">
            </div>
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                    <span class="ml-2 text-sm text-gray-600">Запомнить меня</span>
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-amber-600 hover:underline">Забыли пароль?</a>
                @endif
            </div>
            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2.5 rounded-xl transition shadow-md">
                Войти
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
            Нет аккаунта? <a href="{{ route('register') }}" class="text-amber-600 font-medium hover:underline">Зарегистрироваться</a>
        </p>
    </div>
</div>
@endsection