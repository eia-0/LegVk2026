@extends('layouts.app')

@section('title', 'Каталог - ЛегендаВкуса')

@section('content')
    <div class="mb-4 sm:mb-8">
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
            <div class="space-y-2 mb-4 sm:mb-6">
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
    <div class="mb-4 sm:mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-3 sm:mb-6">Категории</h2>
        <div class="grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-5 gap-0.5 sm:gap-4">
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
            <div class="mb-4 sm:mb-8">
                <h2 class="text-2xl font-bold text-amber-700 mb-2 sm:mb-0">{{ $currentCategoryModel->name }}</h2>
                @if($currentCategoryModel->children->count())
                    <div class="flex flex-wrap gap-2 mt-2 sm:mt-3">
                        <span class="text-gray-600">Подкатегории:</span>
                        @foreach($currentCategoryModel->children as $sub)
                            <a href="{{ route('home', array_merge(request()->except('page'), ['category' => $sub->id])) }}" 
                               class="bg-amber-100 text-amber-800 text-sm px-4 py-1.5 rounded-full font-medium {{ $currentCategory == $sub->id ? 'bg-amber-300' : '' }}">
                                {{ $sub->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('home', request()->except('category', 'page')) }}" class="text-amber-600 underline text-sm mt-2 sm:mt-3 inline-block">← Сбросить фильтр категории</a>
            </div>
        @endif
    @endif

    {{-- Заголовок товаров и панель поиска/сортировки --}}
    <div class="mb-3 sm:mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-4">
            <h2 class="text-2xl font-bold text-gray-800">
                @if(request()->has('products'))
                    Продукты и товары
                @elseif($currentCategory)
                    {{ $currentCategoryModel->name ?? 'Товары' }}
                @else
                    Еда
                @endif
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                {{-- Поиск --}}
                <form action="{{ route('home') }}" method="GET" class="flex">
                    @if($currentCategory)
                        <input type="hidden" name="category" value="{{ $currentCategory }}">
                    @endif
                    @if(request()->has('products'))
                        <input type="hidden" name="products" value="1">
                    @endif
                    <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Найти в ЛегендеВкуса" 
                           class="w-full sm:w-48 px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500">
                    <button type="submit" class="ml-2 bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-sm transition">
                        <span class="text-gray-200">🔍</span>
                    </button>
                </form>
                {{-- Сортировка --}}
                <form action="{{ route('home') }}" method="GET" id="sort-form" class="flex">
                    @if($currentCategory)
                        <input type="hidden" name="category" value="{{ $currentCategory }}">
                    @endif
                    @if($currentSearch)
                        <input type="hidden" name="search" value="{{ $currentSearch }}">
                    @endif
                    @if(request()->has('products'))
                        <input type="hidden" name="products" value="1">
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
    </div>

    {{-- Товары --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-4">
        @forelse($products as $product)
            @php
                $isAvailable = $product->unlimited || ($product->stock !== null && $product->stock > 0);
                $isMadeToOrder = $product->made_to_order;
                $canPurchase = $isAvailable || $isMadeToOrder;
                $isReadyEat = $product->category && $product->category->show_in_ready_eat;
            @endphp
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
                    {{-- Плашки характеристик --}}
                    @if($product->characteristics->isNotEmpty())
                        <div class="absolute bottom-2 left-2 flex flex-wrap gap-1 z-10">
                            @foreach($product->characteristics->sortBy('order') as $characteristic)
                                <span class="text-white text-xs px-1.5 py-0.5 rounded-md leading-tight"
                                      style="background-color: {{ $characteristic->color }}">
                                    {{ $characteristic->icon }} {{ $characteristic->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </a>
                <div class="p-2 sm:p-5 flex flex-col flex-1">
                    <h3 class="text-xs sm:text-lg font-bold text-gray-800 line-clamp-2 leading-tight">{{ $product->name }}</h3>

                    {{-- Категория и время (вертикально) --}}
                    <div class="mt-0.5">
                        <p class="text-xs text-gray-500">{{ $product->category->name ?? '' }}</p>
                        @if($product->preparation_time > 0)
                            <p class="text-xs text-gray-400">⏱ ≈ {{ $product->preparation_time }} мин</p>
                        @elseif($isReadyEat)
                            <p class="text-xs text-green-600 font-medium">🍽️ Готово</p>
                        @endif
                    </div>

                    {{-- Наличие и цена --}}
                    <div class="mt-auto pt-2 flex items-center justify-between">
                        <p class="text-xs sm:text-sm">
                            @if($isAvailable)
                                @if($product->unlimited)
                                    <span class="font-semibold bg-gradient-to-r from-purple-500 to-pink-500 text-transparent bg-clip-text">В наличии</span>
                                @else
                                    <span class="font-semibold bg-gradient-to-r from-purple-500 to-pink-500 text-transparent bg-clip-text">В наличии: {{ $product->stock }} шт.</span>
                                @endif
                            @elseif($isMadeToOrder)
                                <span class="font-semibold bg-gradient-to-r from-amber-500 to-orange-500 text-transparent bg-clip-text">Под заказ</span>
                            @else
                                <span class="text-red-500 font-semibold">Нет в наличии</span>
                            @endif
                        </p>
                        <span class="text-sm sm:text-xl {{ $isMadeToOrder ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-transparent bg-clip-text font-semibold' : 'bg-gradient-to-r from-purple-500 to-pink-500 text-transparent bg-clip-text font-semibold' }}">
                            {{ number_format($product->price, 0) }} ₽
                        </span>
                    </div>

                    {{-- Кнопки --}}
                    <div class="mt-2 sm:mt-3 w-full">
                        @if($canPurchase)
                            @auth
                                @if($isMadeToOrder)
                                    <a href="https://vk.com/legenda_vkusa" target="_blank"
                                       class="block w-full text-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-xs sm:text-sm py-2 px-5 transition-all duration-200 font-semibold hover:-translate-y-0.5 hover:shadow-lg">
                                        Обсудить детали
                                    </a>
                                @else
                                    <template x-if="inCart > 0">
                                        <div class="flex items-center justify-between w-full bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl px-3 py-1 shadow-sm border border-purple-200">
                                            <button @click="addToCart({{ $product->id }}, -1)" class="text-gray-700 font-bold text-lg leading-none w-7 h-7 flex items-center justify-center rounded-full bg-white/80 hover:bg-white transition">−</button>
                                            <span x-text="inCart" class="font-bold text-sm sm:text-base text-purple-700"></span>
                                            <button @click="addToCart({{ $product->id }}, 1)" class="text-gray-700 font-bold text-lg leading-none w-7 h-7 flex items-center justify-center rounded-full bg-white/80 hover:bg-white transition">+</button>
                                        </div>
                                    </template>
                                    <template x-if="inCart === 0">
                                        <button @click="addToCart({{ $product->id }}, 1)" class="w-full bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-semibold rounded-xl text-xs sm:text-sm py-2 px-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                                            В корзину
                                        </button>
                                    </template>
                                @endif
                            @else
                                @if($isMadeToOrder)
                                    <a href="https://vk.com/legenda_vkusa" target="_blank"
                                       class="block w-full text-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-xs sm:text-sm py-2 px-5 transition-all duration-200 font-semibold hover:-translate-y-0.5 hover:shadow-lg">
                                        Обсудить детали
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" 
                                       class="block w-full text-center bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white rounded-xl text-xs sm:text-sm py-2 px-5 transition-all duration-200 font-semibold hover:-translate-y-0.5 hover:shadow-lg">
                                        Войти
                                    </a>
                                @endif
                            @endauth
                        @else
                            <span class="block w-full text-center bg-gradient-to-r from-purple-500/50 to-pink-500/50 text-white rounded-xl text-xs sm:text-sm py-2 px-5 font-semibold cursor-not-allowed">
                                Нет в наличии
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Баннер --}}
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
            <p class="text-gray-500 col-span-full text-center py-8 sm:py-12">Товары не найдены.</p>
        @endforelse
    </div>
    <div class="mt-6 sm:mt-8">
        {{ $products->links() }}
    </div>
@endsection