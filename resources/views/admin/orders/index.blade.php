@extends('admin.layout')
@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Заказы</h1>
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">Пользователь</th>
                    <th class="p-3 text-left">Тип</th>
                    <th class="p-3 text-left">Сумма</th>
                    <th class="p-3 text-left">Статус</th>
                    <th class="p-3 text-left">Дата</th>
                    <th class="p-3 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-t">
                    <td class="p-3">#{{ $order->id }}</td>
                    <td class="p-3">{{ $order->user->name ?? '—' }}</td>
                    <td class="p-3">{{ $order->delivery_type === 'pickup' ? 'Самовывоз' : 'Доставка' }}</td>
                    <td class="p-3">{{ number_format($order->total, 2) }} ₽</td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold text-white {{ $order->status_color }}">
                            {{ $order->status_ru }}
                        </span>
                    </td>
                    <td class="p-3">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    <td class="p-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-amber-600 underline">Просмотр</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-3 text-center text-gray-500">Заказов пока нет.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">
            {{ $orders->links() }}
        </div>
    </div>
@endsection