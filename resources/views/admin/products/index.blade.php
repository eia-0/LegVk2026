@extends('admin.layout')

@section('admin-content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Товары</h1>
        <a href="{{ route('admin.products.create') }}" class="bg-amber-500 text-white px-4 py-2 rounded-full">Добавить</a>
    </div>
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Изображение</th>
                    <th class="p-3 text-left">Название</th>
                    <th class="p-3 text-left">Категория</th>
                    <th class="p-3 text-left">Разделы</th>
                    <th class="p-3 text-left">Цена</th>
                    <th class="p-3 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr class="border-t">
                    <td class="p-3">
                        <img src="{{ $product->image ? asset('storage/'.$product->image) : 'https://via.placeholder.com/40' }}" class="w-10 h-10 object-cover rounded">
                    </td>
                    <td class="p-3">{{ $product->name }}</td>
                    <td class="p-3">{{ $product->category->name ?? '' }}</td>
                    <td class="p-3">
                        @if($product->category)
                            @if($product->category->show_in_catalog && $product->category->show_in_ready_eat)
                                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">Еда, Продукты и товары</span>
                            @elseif($product->category->show_in_catalog)
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full">Еда</span>
                            @elseif($product->category->show_in_ready_eat)
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">Продукты и товары</span>
                            @endif
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="p-3">{{ number_format($product->price, 2) }} ₽</td>
                    <td class="p-3 space-x-2">
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
        <div class="p-3">
            {{ $products->links() }}
        </div>
    </div>
@endsection