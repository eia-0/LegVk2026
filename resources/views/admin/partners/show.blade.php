@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">{{ $partner->full_name }}</h1>
    <p><strong>Телефон:</strong> {{ $partner->phone ?? '—' }}</p>
    <p><strong>Email:</strong> {{ $partner->email ?? '—' }}</p>
    <p><strong>Всего продаж товаров партнёра (общее):</strong> {{ $soldCount }}</p>

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
                        <th class="p-3 text-center">Комиссия</th>
                        <th class="p-3 text-center">Продажи (шт.)</th>
                        <th class="p-3 text-right">Сумма продаж</th>
                        <th class="p-3 text-right">К выплате</th>
                        <th class="p-3 text-center">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partner->products as $product)
                        @php
                            $sold = $product->soldSinceLastPayout();
                            $totalSales = $sold * $product->price;
                            $commission = $product->commission_percent ?? 0;
                            $payout = $totalSales * (1 - $commission / 100);
                        @endphp
                        <tr class="border-t">
                            <td class="p-3">{{ $product->name }}</td>
                            <td class="p-3">{{ number_format($product->price, 2) }} ₽</td>
                            <td class="p-3 text-center">{{ $commission }}%</td>
                            <td class="p-3 text-center">{{ $sold }}</td>
                            <td class="p-3 text-right">{{ number_format($totalSales, 2) }} ₽</td>
                            <td class="p-3 text-right font-semibold {{ $payout > 0 ? 'text-green-600' : 'text-gray-500' }}">{{ number_format($payout, 2) }} ₽</td>
                            <td class="p-3 text-center">
                                <form action="{{ route('admin.partners.reset-payout', [$partner, $product]) }}" method="POST" onsubmit="return confirm('Обнулить продажи для товара «{{ $product->name }}»?')">
                                    @csrf
                                    <button type="submit" class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded hover:bg-red-200 transition">
                                        Обнулить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <a href="{{ route('admin.partners.index') }}" class="inline-block mt-4 text-amber-600 underline">← Назад к партнёрам</a>
@endsection