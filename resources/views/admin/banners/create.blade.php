@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Добавить баннер</h1>

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow max-w-lg">
        @csrf
        <div class="mb-4">
            <label class="block mb-2">Изображение баннера</label>
            <input type="file" name="image" class="w-full border rounded p-2" required>
            <p class="text-xs text-gray-500 mt-1">Рекомендуемый размер: 1080x300 px (или другой пропорциональный).</p>
        </div>
        <div class="mb-4">
            <label class="block mb-2">Интервал смены (сек)</label>
            <input type="number" name="rotation_seconds" value="{{ old('rotation_seconds', 0) }}" min="0" step="1" class="w-full border rounded p-2">
            <p class="text-xs text-gray-500 mt-1">0 – показывать только этот баннер без смены. >0 – интервал в секундах между сменами (если несколько баннеров).</p>
        </div>
        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-full">Создать</button>
    </form>
@endsection