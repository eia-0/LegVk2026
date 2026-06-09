@extends('layouts.app')

@section('title', 'Оформление заказа')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Оформление заказа</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('order.store') }}" method="POST" x-data="{
        deliveryType: 'pickup',
        selectedAddressId: {{ old('delivery_address_id', $addresses->first() ? $addresses->first()->id : 'null') }},
        showNewAddressModal: false,
        newAddress: { latitude: 55.756, longitude: 60.703, street: '', house: '', entrance: '', apartment: '', intercom: '' },
        map: null,
        marker: null,
        pickupMap: null,
        callbackNeeded: {{ old('callback_needed', '1') == '1' ? 'true' : 'false' }},
        minDeliveryDate: '{{ $minDeliveryDate ?? now()->format('Y-m-d') }}',
        minDeliveryTime: '{{ $minDeliveryTime ?? now()->format('H:i') }}',
        deliveryDate: '',
        deliveryTime: '',
        showDeliveryTimePicker: false,
        get minDeliveryDateTimeFormatted() {
            return this.minDeliveryDate + 'T' + this.minDeliveryTime;
        },

        openMapModal() {
            this.showNewAddressModal = true;
            this.$nextTick(() => {
                setTimeout(() => {
                    if (!this.map) {
                        this.map = L.map('map', { attributionControl: false }).setView([55.756, 60.703], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.map);
                        this.marker = L.marker([55.756, 60.703], { draggable: true }).addTo(this.map);
                        this.marker.on('dragend', () => {
                            const pos = this.marker.getLatLng();
                            this.newAddress.latitude = pos.lat;
                            this.newAddress.longitude = pos.lng;
                            this.reverseGeocode(pos.lat, pos.lng);
                        });
                        this.map.on('click', (e) => {
                            this.marker.setLatLng(e.latlng);
                            this.newAddress.latitude = e.latlng.lat;
                            this.newAddress.longitude = e.latlng.lng;
                            this.reverseGeocode(e.latlng.lat, e.latlng.lng);
                        });
                    } else {
                        this.map.invalidateSize();
                    }
                }, 150);
            });
        },

        reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ru`)
                .then(r => r.json())
                .then(data => {
                    if (data.address) {
                        this.newAddress.street = data.address.road || '';
                        this.newAddress.house = data.address.house_number || '';
                    }
                });
        },

        saveNewAddress() {
            document.getElementById('new_address_latitude').value = this.newAddress.latitude;
            document.getElementById('new_address_longitude').value = this.newAddress.longitude;
            document.getElementById('new_address_street').value = this.newAddress.street;
            document.getElementById('new_address_house').value = this.newAddress.house;
            document.getElementById('new_address_entrance').value = this.newAddress.entrance;
            document.getElementById('new_address_apartment').value = this.newAddress.apartment;
            document.getElementById('new_address_intercom').value = this.newAddress.intercom;
            this.selectedAddressId = 'new';
            this.showNewAddressModal = false;
        },

        initPickupMap() {
            if (this.pickupMap) return;
            const mapEl = document.getElementById('pickup-map');
            if (!mapEl) return;
            this.pickupMap = L.map('pickup-map', { attributionControl: false }).setView([{{ $settings->pickup_latitude ?? 55.756 }}, {{ $settings->pickup_longitude ?? 60.703 }}], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.pickupMap);
            L.marker([{{ $settings->pickup_latitude ?? 55.756 }}, {{ $settings->pickup_longitude ?? 60.703 }}]).addTo(this.pickupMap);
        }
    }" x-init="if (deliveryType === 'pickup') $nextTick(() => initPickupMap())">
        @csrf

        {{-- Тип доставки --}}
        <div class="bg-white p-6 rounded-2xl shadow mb-6">
            <label class="block text-lg font-medium mb-3">Способ получения:</label>
            <div class="flex flex-col sm:flex-row gap-4">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="delivery_type" value="pickup" x-model="deliveryType" 
                           @change="if (deliveryType === 'pickup') $nextTick(() => initPickupMap())" class="text-amber-600">
                    <span>Самовывоз</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer" 
                    @if(!$settings->delivery_enabled) title="Доставка отключена" @endif>
                    <input type="radio" name="delivery_type" value="delivery" x-model="deliveryType" 
                        {{ $settings->delivery_enabled ? '' : 'disabled' }} class="text-amber-600">
                    <span>Доставка {{ $settings->delivery_enabled ? '' : '(недоступна)' }}</span>
                </label>
            </div>
            @if(!$settings->delivery_enabled)
                <p class="text-red-500 text-sm mt-2">Доставка временно отключена.</p>
            @endif
        </div>

        {{-- Блок самовывоза --}}
        <div x-show="deliveryType === 'pickup'" class="bg-white p-6 rounded-2xl shadow mb-6">
            <h2 class="text-xl font-semibold mb-3">Самовывоз</h2>
            @if($settings->pickup_address)
                <p class="mb-2">
                    {{ $settings->pickup_address }}
                    @if($settings->pickup_entrance), п. {{ $settings->pickup_entrance }}@endif
                    @if($settings->phone)
                        <br>{{ $settings->phone }}
                    @endif
                </p>
                @if(isset($pickupReadyAt) && $pickupReadyAt->gt(now()))
                    <p class="text-sm text-gray-600 mt-2">Ближайшее время самовывоза: {{ $pickupReadyAt->format('d.m.Y в H:i') }}</p>
                @endif
                @if($settings->pickup_latitude && $settings->pickup_longitude)
                    <div id="pickup-map" style="height: 250px;" class="rounded-lg border"></div>
                @endif
            @else
                <p class="text-gray-500">Адрес самовывоза не указан.</p>
            @endif
        </div>

        {{-- Блок доставки --}}
        <div x-show="deliveryType === 'delivery'" class="bg-white p-6 rounded-2xl shadow mb-6">
            <h2 class="text-xl font-semibold mb-3">Адрес доставки</h2>
            <p class="mb-3">
                Стоимость доставки: 
                <span class="font-bold text-amber-700">{{ $deliveryCost }} ₽</span>
                @if($deliveryCost > 0 && $settings->free_delivery_from)
                    <span class="text-sm text-gray-500"> (бесплатно при заказе от {{ $settings->free_delivery_from }} ₽)</span>
                @elseif($deliveryCost == 0 && $settings->delivery_enabled)
                    <span class="text-green-600 text-sm"> (бесплатно)</span>
                @endif
            </p>

            @if($addresses->count())
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Выберите сохраненный адрес:</label>
                    <select x-model="selectedAddressId" name="delivery_address_id" class="w-full sm:w-1/2 border border-gray-300 rounded-lg p-2">
                        <option value="">-- Выбрать --</option>
                        @foreach($addresses as $addr)
                            <option value="{{ $addr->id }}">
                                {{ $addr->street }}, {{ $addr->house }}, п. {{ $addr->entrance ?? '?' }}, кв. {{ $addr->apartment ?? '?' }}@if($addr->intercom), дом. {{ $addr->intercom }}@endif
                            </option>
                        @endforeach
                        <option value="new">+ Добавить новый адрес</option>
                    </select>
                </div>
            @else
                <p class="text-gray-500 mb-3">У вас пока нет сохранённых адресов.</p>
                <input type="hidden" x-model="selectedAddressId" value="new">
            @endif

            <div x-show="selectedAddressId === 'new' || {{ $addresses->count() ? 'false' : 'true' }}">
                <button type="button" @click="openMapModal()"
                    class="bg-amber-100 text-amber-800 px-4 py-2 rounded-full hover:bg-amber-200">
                    Указать адрес на карте
                </button>
                <div x-show="newAddress.street" class="mt-2 text-gray-700">
                    Выбран: <span x-text="newAddress.street + ', ' + newAddress.house"></span>
                    <span x-show="newAddress.entrance">, п. <span x-text="newAddress.entrance"></span></span>
                    <span x-show="newAddress.apartment">, кв. <span x-text="newAddress.apartment"></span></span>
                    <span x-show="newAddress.intercom">, домофон <span x-text="newAddress.intercom"></span></span>
                </div>
            </div>

            <input type="hidden" name="new_address[latitude]" id="new_address_latitude">
            <input type="hidden" name="new_address[longitude]" id="new_address_longitude">
            <input type="hidden" name="new_address[street]" id="new_address_street">
            <input type="hidden" name="new_address[house]" id="new_address_house">
            <input type="hidden" name="new_address[entrance]" id="new_address_entrance">
            <input type="hidden" name="new_address[apartment]" id="new_address_apartment">
            <input type="hidden" name="new_address[intercom]" id="new_address_intercom">

            {{-- Выбор даты и времени доставки --}}
            <div class="mt-4">
                <button type="button" @click="showDeliveryTimePicker = !showDeliveryTimePicker" class="text-amber-600 underline text-sm">
                    Выбрать конкретную дату и время доставки
                </button>
                <div x-show="showDeliveryTimePicker" class="mt-2 space-y-2">
                    <div>
                        <label class="block text-sm">Дата доставки</label>
                        <input type="date" name="delivery_date" x-model="deliveryDate" :min="minDeliveryDate" class="border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm">Время доставки</label>
                        <input type="time" name="delivery_time" x-model="deliveryTime" :min="deliveryDate === minDeliveryDate ? minDeliveryTime : ''" class="border rounded p-2">
                    </div>
                    <p class="text-xs text-gray-500">Минимальное время доставки: <span x-text="minDeliveryDate + ' ' + minDeliveryTime"></span></p>
                </div>
            </div>
        </div>

        {{-- Примерное время приготовления --}}
        @if($maxPrepTime > 0)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-6">
                ⏱ Примерное время приготовления заказа: <strong>{{ $maxPrepTime }} мин.</strong>
                @if(isset($minDeliveryDate))
                    <span class="block text-sm mt-1">Доставка не ранее {{ \Carbon\Carbon::parse($minDeliveryDate)->format('d.m.Y') }} в {{ $minDeliveryTime }}</span>
                @endif
                @if(isset($pickupReadyAt))
                    <span class="block text-sm mt-1">Самовывоз не ранее {{ $pickupReadyAt->format('d.m.Y в H:i') }}</span>
                @endif
            </div>
        @endif

        {{-- Состав заказа --}}
        <div class="bg-white p-6 rounded-2xl shadow mb-6">
            <h2 class="text-xl font-semibold mb-3">Состав заказа</h2>
            @foreach($cartItems as $item)
                <div class="flex justify-between py-2 border-b">
                    <span>{{ $item->product->name }} × {{ $item->quantity }}</span>
                    <span>{{ $item->product->price * $item->quantity }} ₽</span>
                </div>
            @endforeach
            <div class="flex justify-between font-bold text-lg mt-2">
                <span>Товары</span>
                <span>{{ $total }} ₽</span>
            </div>
            <div class="flex justify-between text-lg mt-1">
                <span>Доставка</span>
                <span>{{ $deliveryCost }} ₽</span>
            </div>
            <div class="flex justify-between font-bold text-xl mt-2">
                <span>Итого</span>
                <span>{{ $total + $deliveryCost }} ₽</span>
            </div>
        </div>

        {{-- Контактные данные --}}
        <div class="bg-white p-6 rounded-2xl shadow mb-6">
            <h2 class="text-xl font-semibold mb-3">Контактные данные</h2>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Номер телефона *</label>
                <input type="tel" name="phone" id="phone-input" value="{{ old('phone') }}" 
                       class="w-full border rounded p-2" placeholder="+7 (___) ___-__-__" required>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" @click="callbackNeeded = !callbackNeeded"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                        :class="callbackNeeded ? 'bg-amber-600' : 'bg-gray-300'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out"
                          :class="callbackNeeded ? 'translate-x-6' : 'translate-x-1'"></span>
                </button>
                <span class="text-sm font-medium">Перезвонить для подтверждения заказа</span>
                <input type="hidden" name="callback_needed" :value="callbackNeeded ? 1 : 0">
            </div>
        </div>

        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-8 rounded-full shadow-lg w-full sm:w-auto">
            Подтвердить заказ
        </button>

        {{-- Модальное окно карты --}}
<div x-cloak x-show="showNewAddressModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50" x-transition @click.away="showNewAddressModal = false">
    <div class="bg-white rounded-2xl w-full max-w-md p-4 mx-2 max-h-screen overflow-auto shadow-2xl">
        <h3 class="text-lg font-bold mb-2">Укажите адрес на карте</h3>
        <div id="map" style="height: 220px;" class="rounded-lg border mb-2"></div>
        <p class="text-xs text-gray-500 mb-2">Перетащите метку или кликните по карте</p>
        
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs text-gray-600 mb-0.5">Улица</label>
                <input type="text" x-model="newAddress.street" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs bg-gray-50" readonly>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-0.5">Дом</label>
                <input type="text" x-model="newAddress.house" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs bg-gray-50" readonly>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-0.5">Подъезд</label>
                <input type="text" x-model="newAddress.entrance" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-0.5">Квартира</label>
                <input type="text" x-model="newAddress.apartment" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div class="col-span-2">
                <label class="block text-xs text-gray-600 mb-0.5">Домофон</label>
                <input type="text" x-model="newAddress.intercom" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs focus:ring-amber-500 focus:border-amber-500" placeholder="Код или номер">
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-3">
            <button type="button" @click="showNewAddressModal = false" class="px-3 py-1.5 text-xs border border-gray-300 rounded-full hover:bg-gray-50 transition">Отмена</button>
            <button type="button" @click="saveNewAddress()" class="px-4 py-1.5 text-xs bg-amber-500 text-white rounded-full hover:bg-amber-600 transition shadow-sm">Сохранить</button>
        </div>
    </div>
</div>
    </form>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone-input');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.startsWith('7') || value.startsWith('8')) {
                        value = value.substring(1);
                    }
                    const part1 = value.substring(0, 3);
                    const part2 = value.substring(3, 6);
                    const part3 = value.substring(6, 8);
                    const part4 = value.substring(8, 10);
                    let formatted = '+7 ';
                    if (part1) formatted += '(' + part1;
                    if (part1.length === 3) formatted += ') ';
                    if (part2) formatted += part2;
                    if (part2.length === 3) formatted += '-';
                    if (part3) formatted += part3;
                    if (part3.length === 2) formatted += '-';
                    if (part4) formatted += part4;
                    e.target.value = formatted.trim();
                });
                phoneInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.replace(/\D/g, '').length <= 1) {
                        e.preventDefault();
                        this.value = '';
                    }
                });
            }
        });
    </script>
@endpush