@extends('layouts.app')
@section('title', 'Заказ #'.$order->id)
@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Заказ №{{ $order->id }}</h1>
            <span id="order-status" class="px-3 py-1 rounded-full text-sm font-semibold text-white {{ $order->status_color }}">
                {{ $order->status_ru }}
            </span>
        </div>
        <p><strong>Тип:</strong> {{ $order->delivery_type === 'pickup' ? 'Самовывоз' : 'Доставка' }}</p>
        @if($order->deliveryAddress)
            <p><strong>Адрес доставки:</strong> {{ $order->deliveryAddress->street }}, д. {{ $order->deliveryAddress->house }}, под. {{ $order->deliveryAddress->entrance ?? '-' }}, кв. {{ $order->deliveryAddress->apartment ?? '-' }}@if($order->deliveryAddress->intercom), домофон {{ $order->deliveryAddress->intercom }}@endif</p>
            <div id="order-map" style="height: 250px;" class="rounded-lg mt-3"></div>
        @endif
        @if($order->delivery_date)
            <p><strong>Дата доставки:</strong> {{ \Carbon\Carbon::parse($order->delivery_date)->format('d.m.Y') }}</p>
        @endif
        @if($order->delivery_time)
            <p><strong>Время доставки:</strong> {{ $order->delivery_time }}</p>
        @endif
        <p><strong>Телефон:</strong> {{ $order->phone ?? 'не указан' }}</p>
        <p><strong>Способ оплаты:</strong> {{ $order->payment_method === 'cash' ? 'Наличные' : 'QR-код' }}</p>
        @if($order->callback_needed)
            <p class="text-amber-600 font-semibold">Требуется звонок для подтверждения</p>
        @else
            <p class="text-gray-500">Звонок не требуется</p>
        @endif
        @if($order->courier_name)
            <p><strong>Курьер:</strong> {{ $order->courier_name }} ({{ $order->courier_phone }})</p>
        @endif

        <div id="cancel-reason" style="{{ $order->status === 'cancelled' && $order->cancellation_reason ? '' : 'display:none;' }}" class="text-red-600 font-semibold mt-2">
            Причина отмены: <span>{{ $order->cancellation_reason }}</span>
        </div>

        <table class="w-full mt-4">
            <thead><tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Сумма</th></tr></thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->price }} ₽</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->price * $item->quantity }} ₽</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p class="text-right font-bold mt-2">Итого: {{ $order->total }} ₽</p>
    </div>

    @if($order->deliveryAddress)
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('order-map', { attributionControl: false }).setView([{{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }}], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([{{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }}]).addTo(map);
        });
    </script>
    @endpush
    @endif

    @push('scripts')
    <script>
        (function() {
            let currentStatus = '{{ $order->status }}';
            const statusUrl = '{{ route('orders.status', $order) }}';

            function updateStatusDisplay(data) {
                if (data.status !== currentStatus) {
                    currentStatus = data.status;
                    const statusSpan = document.getElementById('order-status');
                    if (statusSpan) {
                        statusSpan.textContent = data.status_ru;
                        statusSpan.className = 'px-3 py-1 rounded-full text-sm font-semibold text-white ' + data.status_color;
                    }
                    const cancelReasonBlock = document.getElementById('cancel-reason');
                    if (cancelReasonBlock) {
                        if (data.status === 'cancelled' && data.cancellation_reason) {
                            cancelReasonBlock.style.display = '';
                            cancelReasonBlock.querySelector('span').textContent = data.cancellation_reason;
                        } else {
                            cancelReasonBlock.style.display = 'none';
                        }
                    }
                    if (data.status === 'completed' || data.status === 'cancelled') {
                        clearInterval(pollInterval);
                    }
                }
            }

            function fetchStatus() {
                fetch(statusUrl)
                    .then(response => response.json())
                    .then(data => updateStatusDisplay(data))
                    .catch(err => console.error('Ошибка получения статуса:', err));
            }

            const pollInterval = setInterval(fetchStatus, 10000);
        })();
    </script>
    @endpush
@endsection