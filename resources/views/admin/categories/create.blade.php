@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Новая категория</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow max-w-lg">
        @csrf
        <div class="mb-4">
            <label class="block mb-2">Название</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-2">Родительская категория (необязательно)</label>
            <select name="parent_id" class="w-full border rounded p-2">
                <option value="">Без родителя</option>
                @foreach(\App\Models\Category::whereNull('parent_id')->get() as $cat)
                    <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-2">Изображение (для фона плашки)</label>
            <input type="file" name="image" class="w-full border rounded p-2">
            <p class="text-xs text-gray-500 mt-1">Рекомендуется квадратное изображение.</p>
        </div>
        <div class="mb-4">
            <label class="block mb-2 font-medium">Показывать в разделах:</label>
            <div class="space-y-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="show_in_catalog" value="1" {{ old('show_in_catalog', true) ? 'checked' : '' }} class="text-amber-600">
                    <span>Еда</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="show_in_ready_eat" value="1" {{ old('show_in_ready_eat') ? 'checked' : '' }} class="text-amber-600">
                    <span>Продукты и товары</span>
                </label>
            </div>
        </div>
        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-full">Создать</button>
    </form>
@endsection