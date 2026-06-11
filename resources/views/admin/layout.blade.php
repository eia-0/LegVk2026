@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row gap-6"
         x-data="adminNotifications()"
         x-init="startPolling()">
        <aside class="w-full md:w-64 bg-white rounded-2xl shadow p-4 h-fit">
            <h2 class="text-xl font-bold text-amber-700 mb-3">Админка</h2>
            <ul class="space-y-2">
                <li><a href="{{ route('admin.dashboard') }}" class="block py-1 hover:text-amber-600">Главная</a></li>
                <li><a href="{{ route('admin.categories.index') }}" class="block py-1 hover:text-amber-600">Категории</a></li>
                <li><a href="{{ route('admin.products.index') }}" class="block py-1 hover:text-amber-600">Товары</a></li>
                <li><a href="{{ route('admin.partners.index') }}" class="block py-1 hover:text-amber-600">Партнеры</a></li>
                <li><a href="{{ route('admin.banners.index') }}" class="block py-1 hover:text-amber-600">Реклама</a></li>
                <li><a href="{{ route('admin.users.index') }}" class="block py-1 hover:text-amber-600">Пользователи</a></li>
                <li class="relative">
                    <a href="{{ route('admin.orders.index') }}" class="block py-1 hover:text-amber-600">Заказы</a>
                    <span x-cloak x-show="unseenCount > 0" x-text="unseenCount"
                          class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold"
                          style="display: none;"></span>
                </li>
                <li><a href="{{ route('admin.settings.edit') }}" class="block py-1 hover:text-amber-600">Настройки</a></li>
            </ul>
        </aside>
        <div class="flex-1">
            @yield('admin-content')
        </div>

        {{-- Уведомление о новых заказах --}}
        <div x-show="showNotification" x-transition
             class="fixed bottom-4 right-4 z-50 bg-white border-l-4 border-amber-500 shadow-lg rounded-lg p-4 w-72"
             x-init="$watch('unseenCount', value => { if (value > 0) showNotification = true })">
            <div class="flex items-start">
                <div class="flex-shrink-0 text-amber-500 text-xl">🔔</div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-800">Новый заказ!</p>
                    <p class="text-xs text-gray-500 mt-1">У вас <span x-text="unseenCount"></span> непросмотренных заказов.</p>
                    <div class="mt-2 flex space-x-2">
                        <a href="{{ route('admin.orders.index') }}"
                           class="text-xs bg-amber-500 text-white px-3 py-1 rounded-full hover:bg-amber-600">Перейти</a>
                        <button @click="showNotification = false" class="text-xs text-gray-400 hover:text-gray-600">Скрыть</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function adminNotifications() {
        return {
            unseenCount: {{ \App\Models\Order::where('admin_seen', false)->count() }},
            showNotification: false,
            pollingInterval: null,

            startPolling() {
                if (this.unseenCount > 0) {
                    this.showNotification = true;
                }
                this.pollingInterval = setInterval(() => {
                    this.checkUnseen();
                }, 30000);
            },

            async checkUnseen() {
                try {
                    const response = await fetch('{{ route('admin.orders.unseen-count') }}');
                    const data = await response.json();
                    const previous = this.unseenCount;
                    this.unseenCount = data.count;
                    if (this.unseenCount > previous) {
                        this.showNotification = true;
                    }
                } catch (error) {
                    console.error('Ошибка проверки заказов:', error);
                }
            }
        }
    }
</script>
@endpush