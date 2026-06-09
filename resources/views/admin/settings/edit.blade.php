@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Настройки магазина</h1>
    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white p-6 rounded-2xl shadow max-w-lg" x-data="{
        deliveryEnabled: {{ $settings->delivery_enabled ? 'true' : 'false' }},
        pickupLat: {{ $settings->pickup_latitude ?? 55.756 }},
        pickupLng: {{ $settings->pickup_longitude ?? 60.703 }},
        pickupAddress: '{{ $settings->pickup_address ?? '' }}',
        pickupEntrance: '{{ $settings->pickup_entrance ?? '' }}',
        phone: '{{ $settings->phone ?? '' }}',
        map: null,
        marker: null,
        initMap() {
            setTimeout(() => {
                const mapEl = document.getElementById('pickup-map-settings');
                if (!mapEl) return;
                this.map = L.map(mapEl, { attributionControl: false }).setView([this.pickupLat, this.pickupLng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(this.map);
                this.marker = L.marker([this.pickupLat, this.pickupLng], { draggable: true }).addTo(this.map);
                this.marker.on('dragend', () => {
                    const pos = this.marker.getLatLng();
                    this.pickupLat = pos.lat;
                    this.pickupLng = pos.lng;
                    this.reverseGeocode(pos.lat, pos.lng);
                });
                this.map.on('click', (e) => {
                    this.marker.setLatLng(e.latlng);
                    this.pickupLat = e.latlng.lat;
                    this.pickupLng = e.latlng.lng;
                    this.reverseGeocode(e.latlng.lat, e.latlng.lng);
                });
            }, 200);
        },
        reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ru`)
                .then(r => r.json())
                .then(data => {
                    if (data.address) {
                        const road = data.address.road || '';
                        const house = data.address.house_number || '';
                        this.pickupAddress = (road + ' ' + house).trim();
                    }
                });
        },
        formatPhone(value) {
            let digits = value.replace(/\D/g, '');
            if (digits.startsWith('8')) digits = '7' + digits.substring(1);
            if (digits.startsWith('7')) digits = digits.substring(1);
            const part1 = digits.substring(0, 3);
            const part2 = digits.substring(3, 6);
            const part3 = digits.substring(6, 8);
            const part4 = digits.substring(8, 10);
            let formatted = '+7 ';
            if (part1) formatted += '(' + part1;
            if (part1.length === 3) formatted += ') ';
            if (part2) formatted += part2;
            if (part2.length === 3) formatted += '-';
            if (part3) formatted += part3;
            if (part3.length === 2) formatted += '-';
            if (part4) formatted += part4;
            return formatted.trim();
        }
    }" x-init="initMap()">
        @csrf @method('PATCH')
        
        {{-- Тумблер доставки --}}
        <div class="flex items-center justify-between mb-4">
            <span class="text-gray-700 font-medium">Доставка</span>
            <button type="button" @click="deliveryEnabled = !deliveryEnabled"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                    :class="deliveryEnabled ? 'bg-amber-600' : 'bg-gray-300'">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out"
                      :class="deliveryEnabled ? 'translate-x-6' : 'translate-x-1'"></span>
            </button>
            <input type="hidden" name="delivery_enabled" x-bind:value="deliveryEnabled ? 1 : 0">
        </div>

        <div class="mb-4">
            <label class="block mb-2">Стоимость доставки</label>
            <input type="number" step="0.01" name="delivery_cost" value="{{ $settings->delivery_cost }}" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block mb-2">Бесплатная доставка от суммы заказа (оставьте пустым, если всегда платная)</label>
            <input type="number" step="0.01" name="free_delivery_from" value="{{ $settings->free_delivery_from }}" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block mb-2">Минимальная сумма заказа</label>
            <input type="number" step="0.01" name="min_order_amount" value="{{ $settings->min_order_amount }}" class="w-full border rounded p-2" placeholder="Оставьте пустым, если ограничения нет">
        </div>
        <div class="mb-4">
            <label class="block mb-2">График работы</label>
            <textarea name="opening_hours" rows="3" class="w-full border rounded p-2">{{ $settings->opening_hours }}</textarea>
        </div>

        {{-- Адрес самовывоза с картой --}}
        <div class="mb-4">
            <label class="block mb-2">Адрес самовывоза (перетащите метку на карте)</label>
            <div id="pickup-map-settings" style="height: 300px;" class="rounded-lg border mb-2"></div>
            <input type="text" name="pickup_address" x-model="pickupAddress" class="w-full border rounded p-2 mb-2" placeholder="Адрес определится автоматически" readonly>
            <input type="text" name="pickup_entrance" x-model="pickupEntrance" class="w-full border rounded p-2 mb-2" placeholder="Подъезд (введите вручную)">
            <input type="text" name="phone" x-model="phone" @input="phone = formatPhone(phone)" class="w-full border rounded p-2" placeholder="+7 (___) ___-__-__">
            <input type="hidden" name="pickup_latitude" x-bind:value="pickupLat">
            <input type="hidden" name="pickup_longitude" x-bind:value="pickupLng">
        </div>

        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-full">Сохранить</button>
    </form>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush