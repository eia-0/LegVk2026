@extends('layouts.app')

@section('title', 'Каталог - ЛегендаВкуса')

@section('content')
    <div class="mb-8">
        @php
            $settings = \App\Models\ShopSetting::getSettings();
            // Сохраняем текущие параметры запроса для использования в форме и ссылках
            $currentCategory = request('category');
            $currentSearch = request('search');
            $currentSort = request('sort');
        @endphp

        {{-- Информация о самовывозе и доставке --}}
        <div class="bg-white rounded-2xl shadow-sm p-2.5 sm:p-4 space-y-2 sm:space-y-3 text-xs">
            {{-- График работы + ссылка Партнёрам --}}
            @if($settings->opening_hours)
                <div class="flex items-center justify-between">
                    <p class="text-gray-700 font-medium">
                        График работы: {{ $settings->opening_hours }}
                    </p>
                    <a href="{{ route('cooperation') }}" class="text-gray-700 hover:text-amber-600 font-medium text-xs underline underline-offset-2">
                        Партнерам
                    </a>
                </div>
            @endif
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-green-500 flex-shrink-0"></span>
                    <span class="text-gray-700 font-medium">Самовывоз</span>
                </div>
                <p class="text-gray-500">
                    @if($settings->pickup_address)
                        {{ $settings->pickup_address }}
                        @if($settings->pickup_entrance)
                            , п. {{ $settings->pickup_entrance }}
                        @endif
                        @if($settings->phone)
                            , {{ $settings->phone }}
                        @endif
                    @else
                        Адрес не указан
                    @endif
                </p>
            </div>
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <span class="h-2 w-2 rounded-full {{ $settings->delivery_enabled ? 'bg-green-500' : 'bg-red-500' }} flex-shrink-0"></span>
                    <span class="text-gray-700 font-medium">Доставка</span>
                </div>
                <p class="text-gray-500">
                    @if($settings->delivery_enabled)
                        активна
                        @if($settings->delivery_cost > 0)
                            · {{ $settings->delivery_cost }} ₽
                            @if($settings->free_delivery_from)
                                (0 ₽ от {{ $settings->free_delivery_from }} ₽)
                            @endif
                        @else
                            · бесплатно
                        @endif
                        · только по Озерску
                    @else
                        недоступна
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Плашки активных заказов --}}
    @auth
        @php
            $activeOrders = \App\Models\Order::where('user_id', auth()->id())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest()
                ->get();
        @endphp
        @if($activeOrders->isNotEmpty())
            <div class="space-y-2 mb-6">
                @foreach($activeOrders as $activeOrder)
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-2 sm:p-3 flex items-center justify-between text-xs sm:text-sm">
                        <div class="flex items-center space-x-2 min-w-0">
                            <span class="h-2.5 w-2.5 rounded-full {{ $activeOrder->status_color }} flex-shrink-0"></span>
                            <span class="text-gray-700 truncate">Заказ <strong>№{{ $activeOrder->id }}</strong> — {{ $activeOrder->status_ru }}</span>
                        </div>
                        <a href="{{ route('orders.show', $activeOrder) }}" class="text-amber-600 underline text-xs sm:text-sm font-medium flex-shrink-0 ml-2">Перейти к заказу</a>
                    </div>
                @endforeach
            </div>
        @endif
    @endauth

    {{-- Категории --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Категории</h2>
        <div class="grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-5 gap-1 sm:gap-4">
            @foreach($categories as $cat)
                @php
                    $catImage = $cat->image 
                        ? asset('storage/' . $cat->image) 
                        : 'https://via.placeholder.com/300x200?text=Нет+фото';
                @endphp
                <a href="{{ route('home', array_merge(request()->except('page'), ['category' => $cat->id])) }}" 
                   class="category-card relative bg-cover bg-center rounded-2xl overflow-hidden shadow transition duration-300 h-20 sm:h-28 {{ $currentCategory == $cat->id ? 'ring-2 ring-amber-500' : '' }}"
                   style="background-image: url('{{ $catImage }}');">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-2 text-white">
                        <h3 class="text-xs sm:text-sm font-bold leading-tight">{{ $cat->name }}</h3>
                        @if($cat->children->count())
                            <p class="text-xs opacity-80 hidden sm:block">{{ $cat->children->count() }} подкатегории</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    @if($currentCategory)
        @php
            $currentCategoryModel = \App\Models\Category::find($currentCategory);
        @endphp
        @if($currentCategoryModel)
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-amber-700">{{ $currentCategoryModel->name }}</h2>
                @if($currentCategoryModel->children->count())
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="text-gray-600">Подкатегории:</span>
                        @foreach($currentCategoryModel->children as $sub)
                            <a href="{{ route('home', array_merge(request()->except('page'), ['category' => $sub->id])) }}" 
                               class="bg-amber-100 text-amber-800 text-sm px-4 py-1.5 rounded-full font-medium {{ $currentCategory == $sub->id ? 'bg-amber-300' : '' }}">
                                {{ $sub->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('home', request()->except('category', 'page')) }}" class="text-amber-600 underline text-sm mt-3 inline-block">← Сбросить фильтр категории</a>
            </div>
        @endif
    @endif

    {{-- Заголовок товаров и панель поиска/сортировки --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">
            @if($currentCategory)
                {{ $currentCategoryModel->name ?? 'Товары' }}
            @else
                Все товары
            @endif
        </h2>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            {{-- Поиск --}}
            <form action="{{ route('home') }}" method="GET" class="flex">
                @if($currentCategory)
                    <input type="hidden" name="category" value="{{ $currentCategory }}">
                @endif
                <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Поиск по названию..." 
                       class="w-full sm:w-48 px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500">
                <button type="submit" class="ml-2 bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-sm transition">🔍</button>
            </form>
            {{-- Сортировка --}}
            <form action="{{ route('home') }}" method="GET" id="sort-form" class="flex">
                @if($currentCategory)
                    <input type="hidden" name="category" value="{{ $currentCategory }}">
                @endif
                @if($currentSearch)
                    <input type="hidden" name="search" value="{{ $currentSearch }}">
                @endif
                <select name="sort" onchange="this.form.submit()" 
                        class="w-full sm:w-44 px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500">
                    <option value="">По умолчанию</option>
                    <option value="own" {{ $currentSort == 'own' ? 'selected' : '' }}>Только ЛегендаВкуса</option>
                    <option value="price_asc" {{ $currentSort == 'price_asc' ? 'selected' : '' }}>Цена: по возрастанию</option>
                    <option value="price_desc" {{ $currentSort == 'price_desc' ? 'selected' : '' }}>Цена: по убыванию</option>
                    <option value="name_asc" {{ $currentSort == 'name_asc' ? 'selected' : '' }}>Название: А → Я</option>
                    <option value="name_desc" {{ $currentSort == 'name_desc' ? 'selected' : '' }}>Название: Я → А</option>
                </select>
            </form>
        </div>
    </div>

    {{-- Товары --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-4">
        @forelse($products as $product)
            <div class="product-card bg-white rounded-2xl shadow-md overflow-hidden transition duration-300 flex flex-col"
                 x-data="{ inCart: {{ $cartQuantities[$product->id] ?? 0 }} }"
                 @product-cart-updated.window="if ($event.detail.productId == {{ $product->id }}) inCart = $event.detail.quantity">
                <a href="{{ route('product.show', $product) }}" class="block flex-shrink-0 relative">
                    <img src="{{ $product->image ? asset('storage/' . $product->image) . '?v=' . $product->updated_at->timestamp : 'https://via.placeholder.com/300x200?text=Товар' }}" 
                         class="w-full h-28 sm:h-48 object-cover rounded-t-2xl" alt="{{ $product->name }}" loading="lazy">
                    @if($product->partner_id)
                        <div class="absolute bottom-2 right-2 bg-white rounded-full w-6 h-6 flex items-center justify-center shadow">
                            <img src="{{ asset('images/p.svg') }}" alt="Партнер" class="w-4 h-4">
                        </div>
                    @endif
                </a>
                <div class="p-2 sm:p-5 flex flex-col flex-1">
                    <h3 class="text-xs sm:text-lg font-bold text-gray-800 line-clamp-2 leading-tight">{{ $product->name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $product->category->name ?? '' }}</p>
                    @if($product->preparation_time > 0)
                        <p class="text-xs text-gray-400 mt-0.5">⏱ ≈ {{ $product->preparation_time }} мин</p>
                    @endif

                    {{-- Наличие и цена --}}
                    <div class="mt-auto pt-2 flex items-center justify-between sm:flex-col sm:items-start sm:gap-1">
                        <p class="text-xs sm:text-sm order-1 sm:order-2">
                            @if($product->unlimited)
                                <span class="text-green-600 font-semibold">В наличии</span>
                            @elseif($product->stock !== null && $product->stock > 0)
                                <span class="text-green-600">В наличии: {{ $product->stock }} шт.</span>
                            @else
                                <span class="text-red-500">Нет в наличии</span>
                            @endif
                        </p>
                        <span class="text-sm sm:text-xl text-amber-700 order-2 sm:order-1">
                            {{ number_format($product->price, 0) }} ₽
                        </span>
                    </div>

                    {{-- Кнопка «В корзину» или счётчик --}}
                    @auth
                        <div class="mt-2 sm:mt-3">
                            <div class="sm:hidden h-8 flex items-center justify-center">
                                <template x-if="inCart > 0">
                                    <div class="flex items-center justify-between w-full bg-gray-100 rounded-full px-3 h-full">
                                        <button @click="addToCart({{ $product->id }}, -1)" class="text-gray-700 font-bold text-lg leading-none">−</button>
                                        <span x-text="inCart" class="font-bold text-sm"></span>
                                        <button @click="addToCart({{ $product->id }}, 1)" class="text-gray-700 font-bold text-lg leading-none">+</button>
                                    </div>
                                </template>
                                <template x-if="inCart === 0">
                                    <button @click="addToCart({{ $product->id }}, 1)" class="w-full h-full bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-full text-xs transition flex items-center justify-center">
                                        В корзину
                                    </button>
                                </template>
                            </div>
                            <div class="hidden sm:flex items-center space-x-1">
                                <template x-if="inCart > 0">
                                    <div class="flex items-center space-x-1">
                                        <button @click="addToCart({{ $product->id }}, -1)" class="bg-gray-200 text-gray-700 rounded-full w-7 h-7 flex items-center justify-center text-lg font-bold transition hover:bg-gray-300">−</button>
                                        <span x-text="inCart" class="w-6 text-center font-bold"></span>
                                        <button @click="addToCart({{ $product->id }}, 1)" class="bg-gray-200 text-gray-700 rounded-full w-7 h-7 flex items-center justify-center text-lg font-bold transition hover:bg-gray-300">+</button>
                                    </div>
                                </template>
                                <template x-if="inCart === 0">
                                    <button @click="addToCart({{ $product->id }}, 1)" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-5 rounded-full text-sm transition">
                                        В корзину
                                    </button>
                                </template>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="sm:hidden h-8 flex items-center justify-center w-full bg-amber-500 text-white rounded-full text-xs mt-2">Войти</a>
                    @endauth
                </div>
            </div>

            {{-- Баннер только один раз после 6-го товара (мобильные) --}}
            @if($loop->iteration == 6)
                @php
                    $banners = \App\Models\Banner::where('active', true)->orderBy('order')->get();
                @endphp
                @if($banners->isNotEmpty())
                    <div class="max-[639px]:block hidden col-span-2 my-1">
                        @if($banners->count() == 1 || $banners->first()->interval == 0)
                            <a href="#" class="block">
                                <img src="{{ $banners->first()->image_url }}" class="w-full h-[60px] object-cover rounded-xl" alt="Реклама">
                            </a>
                        @else
                            <div x-data="{ active: 0, banners: {{ json_encode($banners->map(fn($b) => $b->image_url)) }}, interval: null }"
                                 x-init="interval = setInterval(() => { active = (active + 1) % banners.length }, {{ $banners->first()->interval * 1000 }})"
                                 class="relative overflow-hidden rounded-xl h-[60px]">
                                <template x-for="(banner, index) in banners" :key="index">
                                    <img :src="banner" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500"
                                         :class="active === index ? 'opacity-100' : 'opacity-0'">
                                </template>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        @empty
            <p class="text-gray-500 col-span-full text-center py-12">Товары не найдены.</p>
        @endforelse
    </div>
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@endsection