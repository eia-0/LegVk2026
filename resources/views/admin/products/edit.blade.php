@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Редактировать товар</h1>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Название --}}
            <div>
                <label class="block mb-1 font-medium">Название</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full border rounded p-2" required>
            </div>

            {{-- Цена --}}
            <div>
                <label class="block mb-1 font-medium">Цена</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="w-full border rounded p-2" required>
            </div>

            {{-- Время приготовления --}}
            <div>
                <label class="block mb-1 font-medium">Время приготовления (мин)</label>
                <input type="number" name="preparation_time" value="{{ old('preparation_time', $product->preparation_time) }}" class="w-full border rounded p-2">
            </div>

            {{-- Вес --}}
            <div>
                <label class="block mb-1 font-medium">Вес (грамм)</label>
                <input type="number" name="weight" value="{{ old('weight', $product->weight) }}" class="w-full border rounded p-2" placeholder="Например, 250">
            </div>

            {{-- Количество на складе --}}
            <div>
                <label class="block mb-1 font-medium">Количество на складе</label>
                <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="w-full border rounded p-2" placeholder="Оставьте пустым для неограниченного">
                <div class="mt-1 flex items-center space-x-2">
                    <input type="checkbox" name="unlimited" id="unlimited" value="1" {{ old('unlimited', $product->unlimited) ? 'checked' : '' }} class="rounded">
                    <label for="unlimited" class="text-sm">Всегда в наличии (неограниченно)</label>
                </div>
                <div class="mt-1 flex items-center space-x-2">
                    <input type="checkbox" name="made_to_order" id="made_to_order" value="1" {{ old('made_to_order', $product->made_to_order) ? 'checked' : '' }} class="rounded">
                    <label for="made_to_order" class="text-sm">Под заказ</label>
                </div>
            </div>

            {{-- Партнёр --}}
            <div>
                <label class="block mb-1 font-medium">Партнёр (если товар партнёрский)</label>
                <select name="partner_id" class="w-full border rounded p-2">
                    <option value="">— Магазин —</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ old('partner_id', $product->partner_id) == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Категория --}}
            <div>
                <label class="block mb-1 font-medium">Категория</label>
                <select name="category_id" class="w-full border rounded p-2" required>
                    <option value="">Выберите</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Описание --}}
        <div>
            <label class="block mb-1 font-medium">Описание</label>
            <textarea name="description" rows="3" class="w-full border rounded p-2">{{ old('description', $product->description) }}</textarea>
        </div>

        {{-- Технология приготовления --}}
        <div>
            <label class="block mb-1 font-medium">Технология приготовления</label>
            <input type="text" name="cooking_technology" value="{{ old('cooking_technology', $product->cooking_technology) }}" class="w-full border rounded p-2">
        </div>

        {{-- Характеристики --}}
        <div class="md:col-span-2">
            <label class="block mb-1 font-medium">Характеристики</label>
            <div class="flex flex-wrap gap-3">
                @foreach($characteristics as $characteristic)
                    <label class="flex items-center space-x-1 text-sm bg-gray-50 px-3 py-1.5 rounded-lg border cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" name="characteristics[]" value="{{ $characteristic->id }}"
                            {{ $product->characteristics->contains($characteristic->id) ? 'checked' : '' }}
                            class="text-amber-600 rounded">
                        <span>{{ $characteristic->icon }} {{ $characteristic->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Изображение --}}
        <div>
            <label class="block mb-1 font-medium">Изображение</label>
            @if($product->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-24 rounded">
                </div>
            @endif
            <input type="file" name="image" class="w-full border rounded p-2">
        </div>

        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-full">Обновить</button>
    </form>
@endsection