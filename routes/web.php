<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ShopSettingController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\Admin\CharacteristicController;

// Главная страница
Route::get('/', function (Request $request) {
    $showProducts = $request->has('products');

    // Разные ключи кэша для разных разделов
    $cacheKey = $showProducts ? 'home_categories_products' : 'home_categories_food';

    $categories = cache()->remember($cacheKey, 3600, function () use ($showProducts) {
        return \App\Models\Category::with(['children' => function ($q) {
                $q->orderBy('order');
            }])
            ->whereNull('parent_id')
            ->orderBy('order')               // <-- сортировка корневых категорий
            ->when($showProducts, function ($query) {
                $query->where('show_in_ready_eat', true);
            }, function ($query) {
                $query->where('show_in_catalog', true);
            })
            ->get();
    });

    $productsQuery = \App\Models\Product::with('category');

    // Фильтр по категории
    if ($request->has('category')) {
        $categoryId = $request->input('category');
        $category = \App\Models\Category::find($categoryId);
        if ($category) {
            $categoryIds = collect([$category->id]);
            if ($category->children()->exists()) {
                $categoryIds = $categoryIds->merge($category->children->pluck('id'));
            }
            $productsQuery->whereIn('category_id', $categoryIds);
        }
    }

    // Поиск по названию
    if ($request->filled('search')) {
        $search = $request->input('search');
        $productsQuery->where('name', 'like', "%{$search}%");
    }

    // Фильтр по разделу
    if ($showProducts) {
        $productsQuery->whereHas('category', function ($query) {
            $query->where('show_in_ready_eat', true);
        });
    } else {
        $productsQuery->whereHas('category', function ($query) {
            $query->where('show_in_catalog', true);
        });
    }

    // Сортировка товаров
    $sort = $request->input('sort');
    switch ($sort) {
        case 'own':
            $productsQuery->whereNull('partner_id')->latest();
            break;
        case 'price_asc':
            $productsQuery->orderBy('price', 'asc');
            break;
        case 'price_desc':
            $productsQuery->orderBy('price', 'desc');
            break;
        case 'name_asc':
            $productsQuery->orderBy('name', 'asc');
            break;
        case 'name_desc':
            $productsQuery->orderBy('name', 'desc');
            break;
        default:
            $productsQuery->inRandomOrder();
            break;
    }

    $products = $productsQuery->paginate(12)->appends($request->except('page'));

    $cartQuantities = [];
    if (auth()->check()) {
        $cartQuantities = auth()->user()->cartItems->pluck('quantity', 'product_id')->toArray();
    }

    return view('home', compact('categories', 'products', 'cartQuantities'));
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->name('dashboard');

Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/order/create', [OrderController::class, 'create'])->name('order.create');
    Route::post('/order', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/order/{order}/status', [OrderController::class, 'status'])->name('orders.status');
});

// Курьерские маршруты
Route::middleware(['auth', 'courier'])->group(function () {
    Route::get('/courier', [CourierController::class, 'index'])->name('courier.index');
    Route::post('/courier/{order}/accept', [CourierController::class, 'accept'])->name('courier.accept');
    Route::patch('/courier/{order}/status', [CourierController::class, 'updateStatus'])->name('courier.updateStatus');
});

// Админка
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class)->except(['show']);
    // Маршруты для управления порядком категорий
    Route::post('/categories/{category}/move-up', [CategoryController::class, 'moveUp'])->name('categories.moveUp');
    Route::post('/categories/{category}/move-down', [CategoryController::class, 'moveDown'])->name('categories.moveDown');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('partners/{partner}/products/{product}/reset-payout', [PartnerController::class, 'resetPayout'])
         ->name('partners.reset-payout');
    Route::resource('partners', PartnerController::class);
    Route::resource('banners', BannerController::class)->except(['show']);

    // Пользователи
    Route::resource('users', UserController::class)->only(['index']);
    Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/unseen-count', [AdminOrderController::class, 'unseenCount'])->name('orders.unseen-count');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/status', [AdminOrderController::class, 'status'])->name('admin.orders.status');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('settings', [ShopSettingController::class, 'edit'])->name('settings.edit');
    Route::patch('settings', [ShopSettingController::class, 'update'])->name('settings.update');

    Route::resource('characteristics', CharacteristicController::class)->except(['show']);
});

Route::get('/cooperation', function () {
    $settings = \App\Models\ShopSetting::getSettings();
    return view('cooperation', compact('settings'));
})->name('cooperation');

require __DIR__.'/auth.php';