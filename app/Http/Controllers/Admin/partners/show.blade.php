@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">{{ $partner->full_name }}</h1>
    <p><strong>Телефон:</strong> {{ $partner->phone ?? '—' }}</p>
    <p><strong>Email:</strong> {{ $partner->email ?? '—' }}</p>
    <p><strong>Всего продаж товаров партнёра:</strong> {{ $soldCount }}</p>

    <h2 class="text-xl font-semibold mt-6 mb-3">Товары партнёра</h2>
    @if($partner->products->isEmpty())
        <p class="text-gray-500">Нет товаров</p>
    @else
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Название</th>
                        <th class="p-3 text-left">Цена</th>
                        <th class="p-3 text-left">Продажи (шт.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partner->products as $product)
                        @php
                            $sold = $product->orderItems()->whereHas('order', fn($q) => $q->where('status', '!=', 'cancelled'))->sum('quantity');
                        @endphp
                        <tr class="border-t">
                            <td class="p-3">{{ $product->name }}</td>
                            <td class="p-3">{{ $product->price }} ₽</td>
                            <td class="p-3">{{ $sold }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <a href="{{ route('admin.partners.index') }}" class="inline-block mt-4 text-amber-600 underline">← Назад к партнёрам</a>
@endsection