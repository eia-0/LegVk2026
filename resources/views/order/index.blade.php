@extends('layouts.app')
@section('title', 'Мои заказы')
@section('content')
<h1 class="text-3xl font-bold mb-6">Мои заказы</h1>
@forelse($orders as $order)
    <div class="bg-white rounded-xl shadow p-4 mb-3 flex flex-col sm:flex-row sm:justify-between sm:items-center">
        <div class="flex items-center space-x-3">
            <span class="font-bold text-lg">#{{ $order->id }}</span>
            <span class="text-sm text-gray-500">{{ $order->created_at->format('d.m.Y') }}</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold text-white {{ $order->status_color }}">
                {{ $order->status_ru }}
            </span>
        </div>
        <div class="mt-2 sm:mt-0 flex items-center space-x-4">
            <span class="font-bold">{{ $order->total }} ₽</span>
            <a href="{{ route('orders.show', $order) }}" class="text-amber-600 underline text-sm">Подробнее</a>
        </div>
    </div>
@empty
    <p class="text-gray-500">Заказов пока нет.</p>
@endforelse
{{ $orders->links() }}
@endsection