@extends('layouts.app')

@section('title', 'Корзина')

@section('content')
    <h1 class="text-2xl font-semibold text-amber-700 mb-6">Корзина</h1>
    @if($cartItems->isEmpty())
        <div class="text-center py-12 bg-white rounded-2xl shadow">
            <p class="text-gray-500">Ваша корзина пуста</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-6 rounded-full transition">В каталог</a>
        </div>
    @else
        @php
            $settings = \App\Models\ShopSetting::getSettings();
            $minOrder = $settings->min_order_amount;
            $cartTotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

            // Проверка, работаем ли сейчас
            $isOpen = true;
            if ($settings->opening_hours) {
                if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $settings->opening_hours, $matches)) {
                    $openTime = $matches[1];
                    $closeTime = $matches[2];
                    $now = now()->format('H:i');
                    $isOpen = ($now >= $openTime && $now <= $closeTime);
                }
            }
        @endphp

        {{-- Предупреждение о нерабочем времени --}}
        @if(!$isOpen)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl mb-4 text-sm">
                <span class="font-semibold">График работы:</span> сейчас не работаем, но вы можете оформить предзаказ.<br>
                Доставим завтра после открытия или выберите дату доставки на завтра.
            </div>
        @endif

        @if($minOrder !== null && $cartTotal < $minOrder)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl mb-4 text-sm">
                Минимальная сумма заказа: <strong>{{ $minOrder }} ₽</strong>. Добавьте товаров ещё на <strong>{{ $minOrder - $cartTotal }} ₽</strong>.
            </div>
        @endif

        {{-- Таблица для десктопа --}}
        <div class="hidden md:block bg-white rounded-2xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b text-sm text-gray-500">
                    <tr>
                        <th class="p-3 text-left font-medium">Товар</th>
                        <th class="p-3 text-center font-medium">Цена</th>
                        <th class="p-3 text-center font-medium">Кол-во</th>
                        <th class="p-3 text-center font-medium">Сумма</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody id="cart-table-body">
                    @foreach($cartItems as $item)
                    <tr class="border-b hover:bg-gray-50 transition" data-id="{{ $item->id }}">
                        <td class="p-3 flex items-center space-x-3">
                            <div class="relative">
                                <img src="{{ $item->product->image ? asset('storage/'.$item->product->image) : 'https://via.placeholder.com/60x60' }}" class="w-10 h-10 object-cover rounded-lg">
                                @if($item->product->partner_id)
                                    <div class="absolute -top-1 -right-1 bg-white rounded-full w-5 h-5 flex items-center justify-center shadow">
                                        <img src="{{ asset('images/p.svg') }}" alt="Партнёр" class="w-3 h-3">
                                    </div>
                                @endif
                            </div>
                            <span class="text-gray-700">{{ $item->product->name }}</span>
                        </td>
                        <td class="p-3 text-center text-gray-600">{{ $item->product->price }} ₽</td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button onclick="changeQuantity({{ $item->product_id }}, -1)" class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600">−</button>
                                <span class="w-6 text-center text-gray-700">{{ $item->quantity }}</span>
                                <button onclick="changeQuantity({{ $item->product_id }}, 1)" class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600">+</button>
                            </div>
                        </td>
                        <td class="p-3 text-center text-gray-700 font-medium">{{ $item->product->price * $item->quantity }} ₽</td>
                        <td class="p-3 text-center">
                            <button onclick="removeItem({{ $item->id }})" class="text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Мобильные карточки --}}
        <div class="md:hidden space-y-3">
            @foreach($cartItems as $item)
            <div class="bg-white rounded-xl shadow p-3 flex items-center gap-3">
                <div class="relative">
                    <img src="{{ $item->product->image ? asset('storage/'.$item->product->image) : 'https://via.placeholder.com/60x60' }}" class="w-14 h-14 object-cover rounded-xl">
                    @if($item->product->partner_id)
                        <div class="absolute -top-1 -right-1 bg-white rounded-full w-5 h-5 flex items-center justify-center shadow">
                            <img src="{{ asset('images/p.svg') }}" alt="Партнёр" class="w-3 h-3">
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-700 truncate">{{ $item->product->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $item->product->price }} ₽</p>
                    <div class="flex items-center space-x-2 mt-1">
                        <button onclick="changeQuantity({{ $item->product_id }}, -1)" class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-sm text-gray-600">−</button>
                        <span class="w-5 text-center text-gray-700">{{ $item->quantity }}</span>
                        <button onclick="changeQuantity({{ $item->product_id }}, 1)" class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-sm text-gray-600">+</button>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-gray-700 font-medium">{{ $item->product->price * $item->quantity }} ₽</p>
                    <button onclick="removeItem({{ $item->id }})" class="text-gray-400 hover:text-red-500 mt-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Итого и кнопка --}}
        <div class="mt-4 bg-white rounded-2xl shadow p-4 flex justify-between items-center gap-3">
            <span class="text-lg text-gray-700">
                Итого: <span class="text-gray-800 font-normal text-base">{{ number_format($cartTotal, 2) }} ₽</span>
            </span>
            @if($minOrder !== null && $cartTotal < $minOrder)
                <span class="text-gray-500 text-sm">Минимальная сумма не достигнута</span>
            @else
                <a href="{{ route('order.create') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-3 rounded-full transition shadow-md">
                    Продолжить
                </a>
            @endif
        </div>

        <form action="{{ route('cart.clear') }}" method="POST" class="mt-3">
            @csrf
            <button type="submit" class="text-red-500 hover:text-red-700 text-sm underline">Очистить корзину</button>
        </form>

        {{-- Блок "С этим товаром также покупают" --}}
        @php
            // Собираем связанные товары из всех товаров корзины
            $relatedProducts = collect();
            $cartProductIds = $cartItems->pluck('product_id')->toArray();
            foreach ($cartItems as $item) {
                $item->load('product.relatedProducts');
                $relatedProducts = $relatedProducts->merge($item->product->relatedProducts);
            }
            $relatedProducts = $relatedProducts->unique('id')->whereNotIn('id', $cartProductIds)->take(6);
        @endphp
        @if($relatedProducts->isNotEmpty())
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">С этим товаром также покупают</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($relatedProducts as $related)
                        <a href="{{ route('product.show', $related) }}" class="bg-gray-50 rounded-xl p-2 hover:shadow-md transition">
                            <img src="{{ $related->image ? asset('storage/'.$related->image) : 'https://via.placeholder.com/150' }}" 
                                 class="w-full h-24 object-cover rounded-lg mb-1">
                            <h4 class="text-xs font-medium text-gray-700 line-clamp-1">{{ $related->name }}</h4>
                            <p class="text-amber-600 font-medium text-xs mt-0.5">{{ number_format($related->price, 0) }} ₽</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <script>
        async function changeQuantity(productId, delta) {
            try {
                const response = await fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ product_id: productId, quantity: delta })
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.message);
                }
                location.reload();
            } catch (error) {
                console.error(error);
            }
        }

        async function removeItem(cartItemId) {
            if (!confirm('Удалить товар?')) return;
            try {
                const response = await fetch(`/cart/${cartItemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error(error);
            }
        }
    </script>
@endsection