<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ЛегендаВкуса')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        [x-cloak] { display: none !important; }
        .category-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .product-card:hover { transform: scale(1.02); }
        .leaflet-container { height: 100%; width: 100%; }

        .gradient-text {
            background: linear-gradient(135deg, #d97706, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-amber-50 min-h-screen flex flex-col pb-16 md:pb-0"
      x-data="appData()"
      @cart-updated.window="cartCount = $event.detail.count; toastMessage($event.detail.message ?? '')">

    {{-- Toast --}}
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-10" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-10"
         class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 px-6 py-3 bg-amber-600 text-white font-semibold rounded-full shadow-lg text-center"
         x-text="toast.message"
         style="display: none;"
         @click="toast.show = false"></div>

    @php
        $settings = \App\Models\ShopSetting::getSettings();
        $logoContent = '';
        if (file_exists(public_path('images/lvlogo.svg'))) {
            $logoContent = file_get_contents(public_path('images/lvlogo.svg'));
            $logoContent = preg_replace('/<svg/', '<svg width="40" height="40" style="max-width: 100%; height: auto;"', $logoContent, 1);
        }
    @endphp

    {{-- Верхняя навигация (sticky) --}}
    <nav class="bg-white shadow-md sticky top-0 z-40">
        <div class="container mx-auto px-2.5 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14 items-center">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-10 h-10 flex-shrink-0">
                            @if($logoContent)
                                {!! $logoContent !!}
                            @else
                                <img src="{{ asset('images/lvlogo.svg') }}" alt="ЛегендаВкуса" class="w-10 h-10 object-contain">
                            @endif
                        </div>
                        <span class="hidden sm:inline text-xl font-bold tracking-wide gradient-text">ЛегендаВкуса</span>
                        <span class="sm:hidden text-sm font-bold tracking-wide gradient-text">ЛегендаВкуса</span>
                    </a>
                </div>

                {{-- Десктопная навигация --}}
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-amber-600 font-medium flex items-center space-x-1">
                        <span>🍽️</span>
                        <span>Еда</span>
                    </a>
                    <a href="{{ route('home', ['products' => 1]) }}" class="text-gray-700 hover:text-amber-600 font-medium flex items-center space-x-1">
                        <span>🛍️</span>
                        <span>Продукты / товары</span>
                    </a>
                    @auth
                        @if(auth()->user()->role === 'courier')
                            <a href="{{ route('courier.index') }}" class="text-gray-700 hover:text-amber-600 font-medium">Курьерская</a>
                        @endif
                        <a href="{{ route('cart.index') }}" class="text-gray-700 hover:text-amber-600 relative font-medium flex items-center space-x-1">
                            <span>🛒</span>
                            <span>Корзина</span>
                            <span x-show="cartCount > 0" x-text="cartCount"
                                  class="absolute -top-2 -right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center shadow"
                                  style="display: none;"></span>
                        </a>
                        <a href="{{ route('orders.index') }}" class="text-gray-700 hover:text-amber-600 relative font-medium flex items-center space-x-1">
                            <span>📦</span>
                            <span>Мои заказы</span>
                            @if(($activeOrders = auth()->user()->orders()->whereIn('status', ['new','processing'])->count()) > 0)
                                <span class="absolute -top-2 -right-4 bg-green-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center shadow">{{ $activeOrders }}</span>
                            @endif
                        </a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-amber-600 font-semibold">Админка</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-amber-600 font-medium">Выйти</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-300 transition font-medium">Вход</a>
                        <a href="{{ route('register') }}" class="bg-amber-600 text-white px-4 py-2 rounded-full hover:bg-amber-700 transition font-medium">Регистрация</a>
                    @endauth
                </div>

                {{-- Мобильная навигация --}}
                <div class="md:hidden flex items-center space-x-2">
                    @auth
                        @if(auth()->user()->role === 'courier')
                            <a href="{{ route('courier.index') }}" class="text-gray-700 text-sm font-medium">Курьерская</a>
                        @endif
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="text-gray-700 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-4 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('home') }}" class="block px-4 py-2 text-gray-700">Еда</a>
                                <a href="{{ route('cart.index') }}" class="block px-4 py-2">Корзина</a>
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2">Заказы</a>
                                @if(auth()->user()->is_admin)
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2">Админка</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="block w-full text-left px-4 py-2">Выйти</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="bg-gray-200 text-gray-700 px-3 py-1.5 rounded-full text-sm hover:bg-gray-300 transition font-medium">Вход</a>
                        <a href="{{ route('register') }}" class="bg-amber-600 text-white px-3 py-1.5 rounded-full text-sm hover:bg-amber-700 transition font-medium">Регистрация</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-2 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="bg-white shadow-inner mt-8 py-6 hidden md:block">
        <div class="text-center text-gray-600">© 2026 ЛегендаВкуса. Все права защищены.</div>
    </footer>

    {{-- Нижняя мобильная навигация --}}
    @auth
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 shadow-[0_-2px_10px_rgba(0,0,0,0.1)]">
        <div class="flex justify-around items-start h-16 pt-1">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center flex-1 h-full {{ request()->routeIs('home') && !request()->has('products') ? 'text-amber-600' : 'text-gray-500' }}">
                <span class="text-2xl leading-none">🍽️</span>
                <span class="text-xs mt-0.5">Еда</span>
            </a>
            <a href="{{ route('home', ['products' => 1]) }}" class="flex flex-col items-center justify-center flex-1 h-full {{ request()->has('products') ? 'text-amber-600' : 'text-gray-500' }}">
                <span class="text-2xl leading-none">🛍️</span>
                <span class="text-xs text-center mt-0.5">Продукты<br> / товары</span>
            </a>
            <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center flex-1 h-full relative {{ request()->routeIs('cart.*') ? 'text-amber-600' : 'text-gray-500' }}">
                <span class="text-2xl leading-none">🛒</span>
                <span x-show="cartCount > 0" x-text="cartCount"
                      class="absolute top-0 right-1/4 bg-red-500 text-white text-[8px] rounded-full h-3.5 w-3.5 flex items-center justify-center"
                      style="display: none;"></span>
                <span class="text-xs mt-0.5">Корзина</span>
            </a>
            <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center flex-1 h-full relative {{ request()->routeIs('orders.*') ? 'text-amber-600' : 'text-gray-500' }}">
                <span class="text-2xl leading-none">📦</span>
                @if(($activeOrders = auth()->user()->orders()->whereIn('status', ['new','processing'])->count()) > 0)
                    <span class="absolute top-0 right-1/4 bg-green-500 text-white text-[8px] rounded-full h-3.5 w-3.5 flex items-center justify-center">{{ $activeOrders }}</span>
                @endif
                <span class="text-xs mt-0.5">Мои заказы</span>
            </a>
        </div>
    </nav>
    @endauth

    @stack('scripts')

    <script>
        function appData() {
            return {
                cartCount: {{ auth()->check() ? auth()->user()->cartItems->count() : 0 }},
                toast: {
                    show: false,
                    message: '',
                    timer: null
                },
                toastMessage(msg) {
                    if (!msg) return;
                    this.toast.message = msg;
                    this.toast.show = true;
                    clearTimeout(this.toast.timer);
                    this.toast.timer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3000);
                },
                async addToCart(productId, delta = 1) {
                    try {
                        let response = await fetch('{{ route('cart.add') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                product_id: productId,
                                quantity: delta
                            })
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.cartCount = data.cart_count;
                            this.toastMessage(data.message);
                            window.dispatchEvent(new CustomEvent('product-cart-updated', {
                                detail: { productId: data.product_id, quantity: data.item_quantity }
                            }));
                        } else {
                            this.toastMessage(data.message ?? 'Ошибка');
                        }
                    } catch (error) {
                        console.error('Ошибка корзины:', error);
                    }
                }
            };
        }
    </script>
</body>
</html>