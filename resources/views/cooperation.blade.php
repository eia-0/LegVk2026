@extends('layouts.app')

@section('title', 'Партнерам - ЛегендаВкуса')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-xl sm:text-3xl font-bold text-center text-gray-800 mb-4 sm:mb-6">Партнерам</h1>

        {{-- Второй раздел: предложение для розничных и оптовых сетей --}}
        <div class="bg-white rounded-2xl shadow p-3 sm:p-6 mb-3 sm:mb-8">
            <h2 class="text-lg sm:text-2xl font-semibold text-amber-700 mb-2 sm:mb-4">ЛегендаВкуса готова сотрудничать с розничными и оптовыми сетями</h2>
            <p class="text-gray-700 leading-relaxed text-sm sm:text-base mb-3 sm:mb-4">
                Мы предлагаем качественную продукцию собственного производства для реализации в ваших магазинах.
                Если вы представляете розничную или оптовую сеть и заинтересованы в расширении ассортимента,
                мы готовы обсудить условия поставки.
            </p>
            <h3 class="text-base sm:text-xl font-semibold text-gray-800 mb-1 sm:mb-2">Преимущества работы с нами:</h3>
            <ul class="list-disc list-inside text-gray-700 space-y-1 sm:space-y-2 text-sm sm:text-base mb-3 sm:mb-4">
                <li>Широкий ассортимент кондитерских изделий ручной работы</li>
                <li>Стабильное качество и свежесть продукции</li>
                <li>Гибкая ценовая политика и система скидок</li>
                <li>Возможность эксклюзивного представления в вашем регионе</li>
                <li>Маркетинговая поддержка и рекламные материалы</li>
            </ul>
        </div>

        {{-- Первый раздел: размещение продукции партнёров в нашем магазине --}}
        <div class="bg-white rounded-2xl shadow p-3 sm:p-6 mb-3 sm:mb-8">
            <h2 class="text-lg sm:text-2xl font-semibold text-amber-700 mb-2 sm:mb-4">Размещение продукции в магазине ЛегендаВкуса</h2>
            <p class="text-gray-700 leading-relaxed text-sm sm:text-base mb-3 sm:mb-4">
                Мы всегда открыты для новых партнеров и готовы разместить вашу продукцию в нашем магазине.
                Если вы производите пищевую продукцию или товары, мы будем рады видеть ваш товар на витрине.
            </p>
            <h3 class="text-base sm:text-xl font-semibold text-gray-800 mb-1 sm:mb-2">Что мы предлагаем:</h3>
            <ul class="list-disc list-inside text-gray-700 space-y-1 sm:space-y-2 text-sm sm:text-base mb-3 sm:mb-4">
                <li>Размещение ваших товаров в каталоге магазина</li>
                <li>Хранение ваших товаров на собственных складах в Озерске Челябинской области</li>
                <li>Гибкие условия сотрудничества</li>
                <li>Своевременные выплаты</li>
                <li>Реклама ваших товаров среди покупателей</li>
            </ul>
        </div>

        <div class="bg-white rounded-2xl shadow p-3 sm:p-6 mb-3 sm:mb-8">
            <h3 class="text-base sm:text-xl font-semibold text-gray-800 mb-1 sm:mb-2">Как начать?</h3>
            <p class="text-gray-700 leading-relaxed text-sm sm:text-base">
                Свяжитесь с нами любым удобным способом — мы обсудим детали и условия сотрудничества.
            </p>
        </div>

        {{-- Контакты --}}
        <div class="bg-white rounded-2xl shadow p-3 sm:p-6">
            <h2 class="text-lg sm:text-2xl font-semibold text-amber-700 mb-2 sm:mb-4">Контакты для связи</h2>
            <div class="space-y-2 sm:space-y-3 text-gray-700">
                @if($settings->phone)
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <span class="text-amber-600 text-lg sm:text-xl">📞</span>
                        <span class="text-base sm:text-lg">{{ $settings->phone }}</span>
                    </div>
                @endif
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <span class="text-amber-600 text-lg sm:text-xl">✉️</span>
                    <span class="text-base sm:text-lg">legenda.vkusa774@gmail.com</span>
                </div>

                {{-- Социальные сети --}}
                <div class="flex items-center space-x-3 pt-1">
                    <span class="text-amber-600 text-lg sm:text-xl">💬</span>
                    <a href="https://" target="_blank" class="hover:opacity-80 transition">
                        <img src="{{ asset('images/vk.svg') }}" alt="ВК" class="w-7 h-7 sm:w-8 sm:h-8">
                    </a>
                    <a href="https://" target="_blank" class="hover:opacity-80 transition">
                        <img src="{{ asset('images/max.svg') }}" alt="Макс" class="w-7 h-7 sm:w-8 sm:h-8">
                    </a>
                </div>

                @if($settings->pickup_address)
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <span class="text-amber-600 text-lg sm:text-xl">📍</span>
                        <span class="text-base sm:text-lg">{{ $settings->pickup_address }}{{ $settings->pickup_entrance ? ', п. ' . $settings->pickup_entrance : '' }}</span>
                    </div>
                @endif
                @if($settings->opening_hours)
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <span class="text-amber-600 text-lg sm:text-xl">🕒</span>
                        <span class="text-base sm:text-lg">{{ $settings->opening_hours }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection