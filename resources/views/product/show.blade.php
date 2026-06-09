@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow p-6 md:p-10"
         x-data="{ inCart: {{ $cartQuantity }}, quantity: 1 }"
         @product-cart-updated.window="if ($event.detail.productId == {{ $product->id }}) inCart = $event.detail.quantity">
        <div class="flex flex-col md:flex-row gap-8">
            <div class="md:w-1/2">
                <img src="{{ $product->image ? asset('storage/'.$product->image) : 'https://via.placeholder.com/400x400?text=Товар' }}" 
                     class="w-full h-auto rounded-xl object-cover" alt="{{ $product->name }}">
            </div>
            <div class="md:w-1/2">
                <h1 class="text-3xl font-bold text-gray-800">{{ $product->name }}</h1>
                <p class="text-amber-600 text-2xl font-bold mt-2">{{ number_format($product->price, 2) }} ₽</p>
                <p class="text-gray-600 mt-4">{{ $product->description }}</p>
                <p class="mt-2 text-sm text-gray-400">Категория: {{ $product->category->name }}</p>
                @auth
                    <div class="mt-6">
                        <template x-if="inCart === 0">
                            <div class="flex items-center space-x-4">
                                <input type="number" x-model="quantity" value="1" min="1" class="w-16 border border-gray-300 rounded px-2 py-1">
                                <button @click="addToCart({{ $product->id }}, quantity)" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-6 rounded-full transition">
                                    В корзину
                                </button>
                            </div>
                        </template>
                        <template x-if="inCart > 0">
                            <div class="flex items-center space-x-4">
                                <button @click="addToCart({{ $product->id }}, -1)" class="bg-gray-200 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center">−</button>
                                <span x-text="inCart" class="text-xl font-bold w-8 text-center"></span>
                                <button @click="addToCart({{ $product->id }}, 1)" class="bg-gray-200 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center">+</button>
                            </div>
                        </template>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="mt-6 inline-block bg-amber-500 text-white py-2 px-6 rounded-full">Войти, чтобы купить</a>
                @endauth
            </div>
        </div>
    </div>
@endsection