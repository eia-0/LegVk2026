@extends('layouts.app')

@section('title', 'Курьерская – ЛегендаВкуса')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Курьерская – {{ auth()->user()->name }}</h1>

        {{-- Мои активные заказы (delivering) --}}
        @php
            $activeOrders = $myOrders->where('status', 'delivering');
            $completedOrders = $myOrders->where('status', 'completed');
        @endphp

        <h2 class="text-xl font-semibold text-gray-800 mb-3">Активные заказы</h2>
        @if($activeOrders->isNotEmpty())
            <div class="space-y-3 mb-8">
                @foreach($activeOrders as $order)
                    <div class="bg-white rounded-2xl shadow p-3 sm:p-4"
                         x-data="{ showMap: false, map: null, initialized: false }">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-800 text-sm sm:text-base">
                                    Заказ №{{ $order->id }} от {{ $order->user->name ?? '—' }}
                                </p>
                                <p class="text-sm text-gray-600">Телефон: {{ $order->phone }}</p>
                                <p class="text-sm text-gray-600 font-medium mt-1">Откуда:</p>
                                <p class="text-sm text-gray-600">{{ $settings->pickup_address ?? 'Не указан' }}</p>
                                @if($order->deliveryAddress)
                                    <p class="text-sm text-gray-600 font-medium mt-1">Куда:</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $order->deliveryAddress->street }}, д. {{ $order->deliveryAddress->house }}
                                        @if($order->deliveryAddress->entrance), под. {{ $order->deliveryAddress->entrance }}@endif <br>
                                        @if($order->deliveryAddress->apartment) кв. {{ $order->deliveryAddress->apartment }}@endif
                                        @if($order->deliveryAddress->intercom), домофон {{ $order->deliveryAddress->intercom }}@endif
                                    </p>
                                    <button @click="showMap = !showMap; if (!initialized) { $nextTick(() => { initMap('map-my-active-{{ $order->id }}', {{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }}); initialized = true; }); } else { $nextTick(() => { if (map) map.invalidateSize(); }); }"
                                            class="text-amber-600 underline text-sm mt-1">Подробнее (карта)</button>
                                    <div x-show="showMap" x-ref="mapContainer" id="map-my-active-{{ $order->id }}" class="h-48 mt-2 rounded border" style="display: none;"></div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 sm:flex-shrink-0 sm:flex-col sm:items-end">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold text-white bg-orange-500">
                                    Доставляется
                                </span>
                                <form action="{{ route('courier.updateStatus', $order) }}" method="POST" class="flex items-center gap-1 mt-2 sm:mt-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="bg-green-500 text-white px-3 py-2 rounded-full text-sm sm:text-sm">Завершить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 mb-8">У вас пока нет активных заказов.</p>
        @endif

        {{-- Доступные заказы --}}
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Доступные заказы</h2>
        @forelse($orders as $order)
            <div class="bg-white rounded-2xl shadow p-3 sm:p-4 mb-3"
                 x-data="{ showMap: false, map: null, initialized: false }">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-800 text-sm sm:text-base">Заказ №{{ $order->id }}</p>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold text-white
                                {{ $order->status === 'new' || $order->status === 'accepted_cooking' ? 'bg-blue-500' : 'bg-purple-500' }}">
                                {{ $order->status_ru }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">Клиент: {{ $order->user->name ?? '—' }}</p>
                        <p class="text-sm text-gray-600">Телефон: {{ $order->phone }}</p>
                        <p class="text-sm text-gray-600 font-medium mt-1">Откуда:</p>
                        <p class="text-sm text-gray-600">{{ $settings->pickup_address ?? 'Не указан' }}</p>
                        @if($order->deliveryAddress)
                            <p class="text-sm text-gray-600 font-medium mt-1">Куда:</p>
                            <p class="text-sm text-gray-600">
                                {{ $order->deliveryAddress->street }}, д. {{ $order->deliveryAddress->house }}
                                @if($order->deliveryAddress->entrance), под. {{ $order->deliveryAddress->entrance }}@endif
                                @if($order->deliveryAddress->apartment), кв. {{ $order->deliveryAddress->apartment }}@endif
                                @if($order->deliveryAddress->intercom), домофон {{ $order->deliveryAddress->intercom }}@endif
                            </p>
                            <button @click="showMap = !showMap; if (!initialized) { $nextTick(() => { initMap('map-{{ $order->id }}', {{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }}); initialized = true; }); } else { $nextTick(() => { if (map) map.invalidateSize(); }); }"
                                    class="text-amber-600 underline text-sm mt-1">Подробнее (карта)</button>
                            <div x-show="showMap" x-ref="mapContainer" id="map-{{ $order->id }}" class="h-48 mt-2 rounded border" style="display: none;"></div>
                        @endif
                    </div>
                    @if($order->status === 'ready_for_delivery')
                        <div class="sm:flex-shrink-0">
                            <form action="{{ route('courier.accept', $order) }}" method="POST">
                                @csrf
                                <button class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full text-sm transition">
                                    Взять заказ
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500">Нет доступных заказов.</p>
        @endforelse

        {{-- Архив выполненных заказов --}}
        <h2 class="text-xl font-semibold text-gray-800 mb-3 mt-8">Архив выполненных заказов</h2>
        @if($completedOrders->isNotEmpty())
            <div class="space-y-3">
                @foreach($completedOrders as $order)
                    <div class="bg-white rounded-2xl shadow p-3 sm:p-4 opacity-75"
                         x-data="{ showMap: false, map: null, initialized: false }">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 text-sm sm:text-base">
                                Заказ №{{ $order->id }} от {{ $order->user->name ?? '—' }}
                            </p>
                            <p class="text-sm text-gray-600">Телефон: {{ $order->phone }}</p>
                            <p class="text-sm text-gray-600 font-medium mt-1">Откуда:</p>
                            <p class="text-sm text-gray-600">{{ $settings->pickup_address ?? 'Не указан' }}</p>
                            @if($order->deliveryAddress)
                                <p class="text-sm text-gray-600 font-medium mt-1">Куда:</p>
                                <p class="text-sm text-gray-600">
                                    {{ $order->deliveryAddress->street }}, д. {{ $order->deliveryAddress->house }}
                                    @if($order->deliveryAddress->entrance), под. {{ $order->deliveryAddress->entrance }}@endif <br>
                                    @if($order->deliveryAddress->apartment) кв. {{ $order->deliveryAddress->apartment }}@endif
                                    @if($order->deliveryAddress->intercom), домофон {{ $order->deliveryAddress->intercom }}@endif
                                </p>
                                <button @click="showMap = !showMap; if (!initialized) { $nextTick(() => { initMap('map-my-done-{{ $order->id }}', {{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }}); initialized = true; }); } else { $nextTick(() => { if (map) map.invalidateSize(); }); }"
                                        class="text-amber-600 underline text-sm mt-1">Подробнее (карта)</button>
                                <div x-show="showMap" x-ref="mapContainer" id="map-my-done-{{ $order->id }}" class="h-48 mt-2 rounded border" style="display: none;"></div>
                            @endif
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold text-white bg-green-500 mt-2 inline-block">Выполнен</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Нет выполненных заказов.</p>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function initMap(elementId, clientLat, clientLng) {
            const mapEl = document.getElementById(elementId);
            if (!mapEl) return;

            // Если карта уже создана для этого элемента, удаляем её
            if (mapEl._leaflet_id) {
                mapEl._leaflet_id = null;
                mapEl.innerHTML = '';
            }

            const map = L.map(mapEl, { attributionControl: false }).setView([clientLat, clientLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            const greenIcon = new L.Icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            const redIcon = new L.Icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            L.marker([{{ $settings->pickup_latitude ?? 55.756 }}, {{ $settings->pickup_longitude ?? 60.703 }}], { icon: greenIcon })
                .addTo(map)
                .bindPopup('Магазин');

            L.marker([clientLat, clientLng], { icon: redIcon })
                .addTo(map)
                .bindPopup('Клиент');

            // Сохраняем карту в data атрибут, чтобы можно было вызвать invalidateSize позже
            mapEl._leaflet_map = map;

            // Вызываем invalidateSize с небольшой задержкой
            setTimeout(() => {
                map.invalidateSize();
            }, 100);
        }
    </script>
@endpush