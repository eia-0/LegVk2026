@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Редактировать категорию</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow max-w-lg">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block mb-2">Название</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-2">Родительская категория</label>
            <select name="parent_id" class="w-full border rounded p-2">
                <option value="">Без родителя</option>
                @foreach(\App\Models\Category::whereNull('parent_id')->where('id', '!=', $category->id)->get() as $cat)
                    <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-2">Изображение</label>
            @if($category->image_url)
                <div class="mb-2">
                    <img src="{{ $category->image_url }}" class="w-24 h-24 object-cover rounded border" alt="">
                </div>
            @endif
            <input type="file" name="image" class="w-full border rounded p-2">
            <p class="text-xs text-gray-500 mt-1">Оставьте пустым, чтобы не менять.</p>
        </div>
        <div class="mb-4">
            <label class="block mb-2 font-medium">Показывать в разделах:</label>
            <div class="space-y-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="show_in_catalog" value="1" {{ old('show_in_catalog', $category->show_in_catalog) ? 'checked' : '' }} class="text-amber-600">
                    <span>Еда</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="show_in_ready_eat" value="1" {{ old('show_in_ready_eat', $category->show_in_ready_eat) ? 'checked' : '' }} class="text-amber-600">
                    <span>Продукты и товары</span>
                </label>
            </div>
        </div>
        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-full">Обновить</button>
    </form>
@endsection