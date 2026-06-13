@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Характеристики</h1>
    <a href="{{ route('admin.characteristics.create') }}" class="inline-block bg-amber-500 text-white px-4 py-2 rounded-full mb-4">Добавить характеристику</a>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Название</th>
                    <th class="p-3 text-center">Цвет</th>
                    <th class="p-3 text-center">Иконка</th>
                    <th class="p-3 text-center">Порядок</th>
                    <th class="p-3">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($characteristics as $characteristic)
                <tr class="border-t">
                    <td class="p-3">{{ $characteristic->name }}</td>
                    <td class="p-3 text-center">
                        <span class="inline-block w-6 h-6 rounded-full" style="background-color: {{ $characteristic->color }}"></span>
                    </td>
                    <td class="p-3 text-center">{{ $characteristic->icon }}</td>
                    <td class="p-3 text-center">{{ $characteristic->order }}</td>
                    <td class="p-3 space-x-2">
                        <a href="{{ route('admin.characteristics.edit', $characteristic) }}" class="text-blue-600">Ред.</a>
                        <form action="{{ route('admin.characteristics.destroy', $characteristic) }}" method="POST" class="inline">
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