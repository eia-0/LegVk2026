@extends('admin.layout')

@section('admin-content')
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Заказ №{{ $order->id }}</h1>
            <span id="order-status" class="px-3 py-1 rounded-full text-sm font-semibold text-white {{ $order->status_color }}">
                {{ $order->status_ru }}
            </span>
        </div>
        <p><strong>Пользователь:</strong> {{ $order->user->name }} ({{ $order->user->email }})</p>
        <p><strong>Тип:</strong> {{ $order->delivery_type === 'pickup' ? 'Самовывоз' : 'Доставка' }}</p>
        @if($order->deliveryAddress)
            <p><strong>Адрес:</strong> {{ $order->deliveryAddress->street }}, д. {{ $order->deliveryAddress->house }}, под. {{ $order->deliveryAddress->entrance ?? '-' }}, кв. {{ $order->deliveryAddress->apartment ?? '-' }}@if($order->deliveryAddress->intercom), домофон {{ $order->deliveryAddress->intercom }}@endif</p>
            <div id="admin-order-map" style="height: 300px;" class="rounded-lg mt-3 mb-4"></div>
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
            <p class="text-amber-600 font-semibold">Требуется звонок</p>
        @endif

        {{-- Информация о курьере --}}
        @if($order->courier)
            <p><strong>Курьер:</strong> {{ $order->courier->name }} ({{ $order->courier->email }})
               @if($order->courier->phone) — {{ $order->courier->phone }}@endif
            </p>
        @endif

        <div id="cancel-reason" style="{{ $order->status === 'cancelled' && $order->cancellation_reason ? '' : 'display:none;' }}" class="text-red-600 font-semibold mt-2">
            Причина отмены: <span>{{ $order->cancellation_reason }}</span>
        </div>

        <form id="status-form" action="{{ route('admin.orders.status', $order) }}" method="POST" class="mt-4">
            @csrf
            @method('PATCH')
            <div class="flex flex-wrap items-center gap-2">
                <select name="status" id="status-select" class="border rounded p-2">
                    <option value="new" {{ $order->status=='new'?'selected':'' }}>Новый</option>
                    <option value="accepted_cooking" {{ $order->status=='accepted_cooking'?'selected':'' }}>Принят и готовится</option>
                    @if($order->delivery_type === 'pickup')
                        <option value="ready_for_pickup" {{ $order->status=='ready_for_pickup'?'selected':'' }}>Ожидает получения</option>
                    @else
                        <option value="ready_for_delivery" {{ $order->status=='ready_for_delivery'?'selected':'' }}>Ожидает доставки</option>
                        <option value="delivering" {{ $order->status=='delivering'?'selected':'' }}>Доставляется</option>
                    @endif
                    <option value="completed" {{ $order->status=='completed'?'selected':'' }}>Выполнен</option>
                    <option value="cancelled" {{ $order->status=='cancelled'?'selected':'' }}>Отменён</option>
                </select>

                <div id="cancel-reason-select" style="{{ $order->status === 'cancelled' ? '' : 'display:none;' }}">
                    <select name="cancellation_reason" class="border rounded p-2 text-sm">
                        <option value="">-- Причина --</option>
                        <option value="Нет нужных ингредиентов" {{ $order->cancellation_reason === 'Нет нужных ингредиентов' ? 'selected' : '' }}>Нет нужных ингредиентов</option>
                        <option value="Слишком далеко" {{ $order->cancellation_reason === 'Слишком далеко' ? 'selected' : '' }}>Слишком далеко</option>
                        <option value="Технические проблемы" {{ $order->cancellation_reason === 'Технические проблемы' ? 'selected' : '' }}>Технические проблемы</option>
                    </select>
                </div>

                <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded hover:bg-amber-600 transition">
                    Обновить статус
                </button>
            </div>
        </form>

        <table class="w-full mt-4">
            <thead>
                <tr>
                    <th class="p-3 text-left">Товар</th>
                    <th class="p-3 text-left">Цена</th>
                    <th class="p-3 text-center">Кол-во</th>
                    <th class="p-3 text-right">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr class="border-t">
                    <td class="p-3">
                        @if($item->product)
                            <a href="{{ route('product.show', $item->product) }}" class="text-amber-600 hover:underline font-medium">
                                {{ $item->product->name }}
                            </a>
                        @else
                            {{ $item->product->name ?? 'Товар удалён' }}
                        @endif
                    </td>
                    <td class="p-3">{{ $item->price }} ₽</td>
                    <td class="p-3 text-center">{{ $item->quantity }}</td>
                    <td class="p-3 text-right">{{ $item->price * $item->quantity }} ₽</td>
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
            var map = L.map('admin-order-map', { attributionControl: false }).setView([{{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }}], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([{{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }}]).addTo(map);

            const statusSelect = document.getElementById('status-select');
            const cancelBlock = document.getElementById('cancel-reason-select');
            if (statusSelect && cancelBlock) {
                statusSelect.addEventListener('change', function() {
                    if (this.value === 'cancelled') {
                        cancelBlock.style.display = '';
                    } else {
                        cancelBlock.style.display = 'none';
                    }
                });
            }
        });
    </script>
    @endpush
    @endif

    @push('scripts')
    <script>
        (function() {
            let currentStatus = '{{ $order->status }}';
            const statusUrl = '{{ route('admin.orders.status', $order) }}';

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