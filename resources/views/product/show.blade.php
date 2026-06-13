@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow p-4 md:p-6"
         x-data="{ inCart: {{ $cartQuantity }}, quantity: 1 }"
         @product-cart-updated.window="if ($event.detail.productId == {{ $product->id }}) inCart = $event.detail.quantity">
        
        {{-- Кнопка "Назад" --}}
        <button onclick="history.back()" class="mb-4 text-sm text-gray-500 hover:text-amber-600 transition flex items-center gap-1">
            ← Назад
        </button>

        <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-1/2 relative">
                <img src="{{ $product->image ? asset('storage/'.$product->image) : 'https://via.placeholder.com/400x400?text=Товар' }}" 
                     class="w-full h-auto rounded-xl object-cover" alt="{{ $product->name }}">
                @if($product->partner_id)
                    <div class="absolute bottom-3 right-3 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow">
                        <img src="{{ asset('images/p.svg') }}" alt="Партнёр" class="w-5 h-5">
                    </div>
                @endif
                {{-- Плашки характеристик --}}
                @if($product->characteristics->isNotEmpty())
                    <div class="absolute bottom-3 left-3 flex flex-wrap gap-1 z-10">
                        @foreach($product->characteristics->sortBy('order') as $characteristic)
                            <span class="text-white text-xs px-2 py-1 rounded-md leading-tight"
                                  style="background-color: {{ $characteristic->color }}">
                                {{ $characteristic->icon }} {{ $characteristic->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="md:w-1/2">
                <h1 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $product->partner_id ? 'Товар партнера' : 'Товар магазина' }}
                </p>
                <p class="text-xl font-semibold mt-2 {{ $product->made_to_order ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-transparent bg-clip-text' : 'bg-gradient-to-r from-purple-500 to-pink-500 text-transparent bg-clip-text' }}">
                    {{ number_format($product->price, 2) }} ₽
                </p>
                <p class="text-gray-600 text-sm mt-3">{{ $product->description }}</p>
                
                {{-- Технология приготовления (только для админа) --}}
                @if(auth()->check() && auth()->user()->is_admin && $product->cooking_technology)
                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <h3 class="text-sm font-semibold text-amber-800 mb-1">Технология приготовления</h3>
                        <p class="text-sm text-gray-700">{{ $product->cooking_technology }}</p>
                    </div>
                @endif

                <p class="mt-2 text-xs text-gray-400">Категория: {{ $product->category->name }}</p>
                @if($product->preparation_time > 0)
                    <p class="mt-1 text-xs text-gray-400">⏱ ≈ {{ $product->preparation_time }} мин</p>
                @endif

                @auth
                    <div class="mt-4">
                        @if($product->made_to_order)
                            {{-- «Обсудить детали» янтарная --}}
                            <a href="https://vk.com/legenda_vkusa" target="_blank"
                               class="inline-block bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium py-2 px-6 rounded-full text-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                                Обсудить детали
                            </a>
                        @else
                            {{-- «В корзину» фиолетово‑розовая --}}
                            <template x-if="inCart === 0">
                                <div class="flex items-center space-x-3">
                                    <input type="number" x-model="quantity" value="1" min="1" class="w-14 border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                                    <button @click="addToCart({{ $product->id }}, quantity)" class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-medium py-2 px-5 rounded-full text-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                                        В корзину
                                    </button>
                                </div>
                            </template>
                            <template x-if="inCart > 0">
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center space-x-1 bg-gradient-to-r from-purple-50 to-pink-50 rounded-full px-3 py-1 shadow-sm border border-purple-200">
                                        <button @click="addToCart({{ $product->id }}, -1)" class="text-gray-700 rounded-full w-7 h-7 flex items-center justify-center bg-white/80 hover:bg-white transition">−</button>
                                        <span x-text="inCart" class="text-lg font-semibold w-6 text-center text-purple-700"></span>
                                        <button @click="addToCart({{ $product->id }}, 1)" class="text-gray-700 rounded-full w-7 h-7 flex items-center justify-center bg-white/80 hover:bg-white transition">+</button>
                                    </div>
                                </div>
                            </template>
                        @endif
                    </div>
                @else
                    <div class="mt-4">
                        @if($product->made_to_order)
                            <a href="https://vk.com/legenda_vkusa" target="_blank"
                               class="inline-block bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium py-2 px-6 rounded-full text-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                                Обсудить детали
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="inline-block bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-medium py-2 px-6 rounded-full text-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                                Войти, чтобы купить
                            </a>
                        @endif
                    </div>
                @endauth
            </div>
        </div>

        {{-- Блок "С этим также берут" --}}
        @if($product->relatedProducts->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">С этим также берут</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($product->relatedProducts as $related)
                        <a href="{{ route('product.show', $related) }}" class="bg-gray-50 rounded-xl p-3 hover:shadow-md transition">
                            <img src="{{ $related->image ? asset('storage/'.$related->image) : 'https://via.placeholder.com/150' }}" 
                                 class="w-full h-32 object-cover rounded-lg mb-2">
                            <h4 class="text-sm font-medium text-gray-800 line-clamp-1">{{ $related->name }}</h4>
                            <p class="text-amber-600 font-semibold text-sm mt-1">{{ number_format($related->price, 0) }} ₽</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection