@extends('admin.layout')
@section('admin-content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Товары</h1>
        <a href="{{ route('admin.products.create') }}" class="bg-amber-500 text-white px-4 py-2 rounded-full">Добавить</a>
    </div>
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr><th class="p-3 text-left">Изображение</th><th>Название</th><th>Категория</th><th>Цена</th><th>Действия</th></tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr class="border-t">
                    <td class="p-3"><img src="{{ $product->image ? asset('storage/'.$product->image) : 'https://via.placeholder.com/40' }}" class="w-10 h-10 object-cover rounded"></td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '' }}</td>
                    <td>{{ $product->price }} ₽</td>
                    <td class="space-x-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600">Ред.</a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-red-600" onclick="return confirm('Удалить?')">Уд.</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">{{ $products->links() }}</div>
    </div>
@endsection