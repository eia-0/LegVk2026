@extends('layouts.app')

@section('title', 'Курьерская – ЛегендаВкуса')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Курьерская – {{ auth()->user()->name }}</h1>

        {{-- Мои активные заказы --}}
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Мои активные заказы</h2>
        @if($myOrders->isNotEmpty())
            <div class="space-y-3 mb-8">
                @foreach($myOrders as $order)
                    <div class="bg-white rounded-2xl shadow p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">Заказ №{{ $order->id }} от {{ $order->user->name ?? '—' }}</p>
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
                                    <button onclick="document.getElementById('map-my-{{ $order->id }}').classList.toggle('hidden')"
                                            class="text-amber-600 underline text-sm mt-1">Подробнее (карта)</button>
                                    <div id="map-my-{{ $order->id }}" class="hidden h-48 mt-2 rounded border"></div>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold text-white {{ $order->status === 'delivering' ? 'bg-orange-500' : 'bg-green-500' }}">
                                    {{ $order->status === 'delivering' ? 'Доставляется' : 'Выполнен' }}
                                </span>
                                <form action="{{ route('courier.updateStatus', $order) }}" method="POST" class="flex items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="border rounded px-2 py-1 text-sm">
                                        <option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>Доставляется</option>
                                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Выполнен</option>
                                    </select>
                                    <button type="submit" class="bg-amber-500 text-white px-2 py-1 rounded text-sm">Ок</button>
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
            <div class="bg-white rounded-2xl shadow p-4 mb-3">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-800">Заказ №{{ $order->id }}</p>
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
                            <button onclick="document.getElementById('map-{{ $order->id }}').classList.toggle('hidden')"
                                    class="text-amber-600 underline text-sm mt-1">Подробнее (карта)</button>
                            <div id="map-{{ $order->id }}" class="hidden h-48 mt-2 rounded border"></div>
                        @endif
                    </div>
                    @if($order->status === 'ready_for_delivery')
                        <form action="{{ route('courier.accept', $order) }}" method="POST">
                            @csrf
                            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full text-sm transition">Взять заказ</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500">Нет доступных заказов.</p>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($orders as $order)
                @if($order->deliveryAddress)
                    initMap('map-{{ $order->id }}', {{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }});
                @endif
            @endforeach

            @foreach($myOrders as $order)
                @if($order->deliveryAddress)
                    initMap('map-my-{{ $order->id }}', {{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }});
                @endif
            @endforeach
        });

        function initMap(elementId, clientLat, clientLng) {
            const mapEl = document.getElementById(elementId);
            if (!mapEl) return;

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
            L.marker([{{ $settings->pickup_latitude ?? 55.756 }}, {{ $settings->pickup_longitude ?? 60.703 }}], { icon: greenIcon })
                .addTo(map)
                .bindPopup('Магазин');

            const redIcon = new L.Icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            L.marker([clientLat, clientLng], { icon: redIcon })
                .addTo(map)
                .bindPopup('Клиент');
        }
    </script>
@endpush