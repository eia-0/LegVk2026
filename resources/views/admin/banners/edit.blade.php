@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Редактировать баннер</h1>

    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow max-w-lg">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block mb-2">Изображение</label>
            @if($banner->image_url)
                <img src="{{ $banner->image_url }}" class="h-20 object-cover rounded mb-2">
            @endif
            <input type="file" name="image" class="w-full border rounded p-2">
            <p class="text-xs text-gray-500 mt-1">Оставьте пустым, чтобы не менять. Рекомендуемый размер: 720×200 px</p>
        </div>
        <div class="mb-4">
            <label class="block mb-2">Интервал смены (сек)</label>
            <input type="number" name="interval" value="{{ $banner->interval }}" min="0" class="w-full border rounded p-2">
        </div>
        <label class="flex items-center space-x-2 mb-4">
            <input type="checkbox" name="active" value="1" {{ $banner->active ? 'checked' : '' }} class="text-amber-600">
            <span>Баннер активен</span>
        </label>
        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-full">Обновить</button>
    </form>
@endsection