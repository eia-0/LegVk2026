@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Редактировать товар</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow max-w-2xl">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Название</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block mb-1">Цена</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block mb-1">Время приготовления (мин)</label>
                <input type="number" name="preparation_time" value="{{ old('preparation_time', $product->preparation_time) }}" class="w-full border rounded p-2" min="0">
            </div>
            <div>
                <label class="block mb-1">Количество на складе</label>
                <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="w-full border rounded p-2" min="0" placeholder="Пусто, если неограничено">
            </div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" name="unlimited" id="unlimited" value="1" {{ old('unlimited', $product->unlimited) ? 'checked' : '' }} class="text-amber-600">
                <label for="unlimited">Всегда в наличии (неограниченно)</label>
            </div>
            <div>
                <label class="block mb-1">Партнёр (если товар партнёрский)</label>
                <select name="partner_id" class="w-full border rounded p-2">
                    <option value="">— Магазин —</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ old('partner_id', $product->partner_id) == $partner->id ? 'selected' : '' }}>{{ $partner->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block mb-1">Описание</label>
                <textarea name="description" rows="3" class="w-full border rounded p-2">{{ old('description', $product->description) }}</textarea>
            </div>
            <div>
                <label class="block mb-1">Категория</label>
                <select name="category_id" class="w-full border rounded p-2" required>
                    @foreach(\App\Models\Category::with('children')->get() as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @foreach($cat->children as $child)
                            <option value="{{ $child->id }}" {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>— {{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1">Изображение</label>
                <input type="file" name="image" class="w-full border rounded p-2">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" class="w-16 mt-2 rounded">
                @endif
            </div>
        </div>
        <button type="submit" class="mt-4 bg-amber-500 text-white px-6 py-2 rounded-full">Обновить</button>
    </form>
@endsection