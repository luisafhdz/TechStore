<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Público: catálogo
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('catalog.index'));
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/{product}', [CatalogController::class, 'show'])->name('catalog.show');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Cliente: carrito (requiere sesión)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrito/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/carrito/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrito/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
});

/*
|--------------------------------------------------------------------------
| Administración (solo rol admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.products.index'))->name('home');

    Route::resource('products', ProductController::class)->except('show');
    Route::patch('products/{product}/stock', [ProductController::class, 'updateStock'])->name('products.stock');

    Route::resource('categories', CategoryController::class)->except('show');
});

/*
|--------------------------------------------------------------------------
| Pedidos (CRUD inicial) - ahora protegido para administradores.
| Se rediseñará en la Fase 3 (pedidos reales del carrito).
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/orders', [OrderController::class, 'home'])->name('orders.home');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
});
