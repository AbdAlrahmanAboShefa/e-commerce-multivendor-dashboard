<?php


use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\CartItemsController;
use App\Http\Controllers\CtegorieController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\SalesDashboardController;
use App\Models\Product;
use App\Http\Controllers\adminDashboardController;
use App\Http\Controllers\SelerRequestController;
use App\Http\Controllers\OrderItemsController;              





Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user()->load('roles');
});
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/dashboard', [adminDashboardController::class, 'index']);
    Route::apiResource('categories', CtegorieController::class)->only(['index', 'store','update','destroy']);
    Route::apiResource('seler_requests', SelerRequestController::class)->only(['index']);
    Route::post('/seler_requests/{id}/approve', [SelerRequestController::class, 'approve']);
    Route::post('/seler_requests/{id}/reject', [SelerRequestController::class, 'reject']);
});
Route::prefix('seller')->middleware(['auth:sanctum', 'role:seller'])->group(function () {
    Route::get('/dashboard', [SalesDashboardController::class, 'index']);
    Route::apiResource('products', ProductController::class)->only(['index', 'store','update','destroy','show']);
    Route::patch('/order_items/{id}/status', [OrderItemsController::class, 'updateStatus']);
});

// Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/carts', [CartsController::class, 'index']);
    Route::post('/carts', [CartsController::class, 'destroy']);
    Route::post('/cart/items', [CartItemsController::class, 'store']);
    Route::put('/cart/items/{id}', [CartItemsController::class, 'update']);
    Route::delete('/cart/items/{id}', [CartItemsController::class, 'destroy']);
    Route::get('/categories', [CtegorieController::class, 'index']);
    Route::get('/categories/{id}', [CtegorieController::class, 'show']);
    Route::post('/orders', [OrdersController::class, 'checkout']);
    Route::get('/orders', [OrdersController::class, 'index']);
    Route::delete('/orders/{id}', [OrdersController::class, 'delete']);
    Route::post('/seler_requests', [SelerRequestController::class, 'store']);
});
require __DIR__ . '/auth.php';
