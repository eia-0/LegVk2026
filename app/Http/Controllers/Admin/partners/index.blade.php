@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Партнёры</h1>
    <a href="{{ route('admin.partners.create') }}" class="inline-block bg-amber-500 text-white px-4 py-2 rounded-full mb-4">Добавить партнёра</a>
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">ФИО</th>
                    <th class="p-3 text-left">Телефон</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Товары</th>
                    <th class="p-3 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($partners as $partner)
                <tr class="border-t">
                    <td class="p-3">
                        <a href="{{ route('admin.partners.show', $partner) }}" class="text-amber-600 underline font-medium">
                            {{ $partner->full_name }}
                        </a>
                    </td>
                    <td class="p-3">{{ $partner->phone ?? '—' }}</td>
                    <td class="p-3">{{ $partner->email ?? '—' }}</td>
                    <td class="p-3">{{ $partner->products_count }}</td>
                    <td class="p-3 space-x-2">
                        <a href="{{ route('admin.partners.edit', $partner) }}" class="text-blue-600">Ред.</a>
                        <form action="{{ route('admin.partners.destroy', $partner) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-red-600" onclick="return confirm('Удалить?')">Уд.</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">{{ $partners->links() }}</div>
    </div>
@endsection