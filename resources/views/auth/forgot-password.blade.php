@extends('layouts.app')

@section('title', 'Восстановление пароля - ЛегендаВкуса')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 w-full max-w-md">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 text-center">Забыли пароль?</h1>
            <p class="text-sm text-gray-500 mb-6 text-center">
                Нет проблем. Просто укажите ваш адрес электронной почты, и мы пришлём вам ссылку для сброса пароля.
            </p>

            {{-- Статус сессии (например, после отправки ссылки) --}}
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Адрес электронной почты</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('email') border-red-500 @enderror"
                           placeholder="you@example.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-medium py-2.5 px-4 rounded-full transition text-sm">
                    Отправить ссылку для сброса
                </button>

                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-amber-600 hover:underline">← Вернуться ко входу</a>
                </div>
            </form>
        </div>
    </div>
@endsection