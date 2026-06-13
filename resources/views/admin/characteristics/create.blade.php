@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Добавить характеристику</h1>

    <form action="{{ route('admin.characteristics.store') }}" method="POST" class="bg-white p-6 rounded-2xl shadow max-w-lg">
        @csrf
        <div class="mb-4">
            <label class="block mb-2">Название</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-2">Цвет (HEX)</label>
            <input type="color" name="color" value="{{ old('color', '#6b7280') }}" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block mb-2">Иконка (эмодзи)</label>
            <input type="text" name="icon" value="{{ old('icon') }}" class="w-full border rounded p-2" placeholder="Например: 🍃">
        </div>
        <div class="mb-4">
            <label class="block mb-2">Порядок</label>
            <input type="number" name="order" value="{{ old('order', 0) }}" class="w-full border rounded p-2">
        </div>
        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-full">Создать</button>
    </form>
@endsection