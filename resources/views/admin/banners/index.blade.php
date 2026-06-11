@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Рекламные баннеры</h1>
    <a href="{{ route('admin.banners.create') }}" class="inline-block bg-amber-500 text-white px-4 py-2 rounded-full mb-4">Добавить баннер</a>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Изображение</th>
                    <th class="p-3 text-left">Интервал (сек)</th>
                    <th class="p-3 text-left">Статус</th>
                    <th class="p-3 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banners as $banner)
                <tr class="border-t">
                    <td class="p-3">
                        <img src="{{ asset('storage/'.$banner->image) }}" class="w-20 h-16 object-cover rounded">
                    </td>
                    <td class="p-3">{{ $banner->rotation_seconds > 0 ? $banner->rotation_seconds . ' сек' : 'Один показ' }}</td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $banner->active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $banner->active ? 'Активен' : 'Неактивен' }}
                        </span>
                    </td>
                    <td class="p-3 space-x-2">
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="text-blue-600">Ред.</a>
                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-red-600" onclick="return confirm('Удалить?')">Уд.</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection