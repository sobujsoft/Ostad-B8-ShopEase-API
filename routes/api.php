<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\V1\CartController;
use App\Http\Controllers\V1\CategoryController;
use App\Http\Controllers\V1\OrderController;
use App\Http\Controllers\V1\ProductController;
use App\Http\Controllers\V1\HeroBannerController;
use App\Http\Controllers\V1\SectionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Storefront Routes (Public)
Route::prefix('storefront')->group(function () {
    Route::get('/hero-banners', [HeroBannerController::class, 'storefront']);
    Route::get('/categories', [CategoryController::class, 'storefront']);
    Route::get('/sections', [SectionController::class, 'storefront']);
    Route::get('/products', [ProductController::class, 'storefront']);
    Route::get('/products/{slug}', [ProductController::class, 'storefrontShow']);

    Route::post('/register', [AuthController::class, 'customerRegister']);
    Route::post('/login', [AuthController::class, 'customerLogin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'customerLogout']);
        Route::get('/profile', [AuthController::class, 'customerProfile']);
        Route::post('/profile', [AuthController::class, 'customerProfileUpdate']);

        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/', [CartController::class, 'store']);
            Route::patch('/{cart}', [CartController::class, 'update']);
            Route::delete('/{cart}', [CartController::class, 'destroy']);
            Route::delete('/', [CartController::class, 'clear']);
        });

        Route::post('/checkout', [OrderController::class, 'placeOrder']);
        Route::get('/orders', [OrderController::class, 'myOrders']);
        Route::get('/orders/{orderNumber}', [OrderController::class, 'myOrderShow']);
    });
});

// API V1 Routes
Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/{category}', [CategoryController::class, 'show']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::post('/{category}', [CategoryController::class, 'update']);
            Route::delete('/{category}', [CategoryController::class, 'destroy']);
        });

        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/{product}', [ProductController::class, 'show']);
            Route::post('/{product}', [ProductController::class, 'update']);
            Route::delete('/{product}', [ProductController::class, 'destroy']);
            Route::patch('/{product}/toggle-active', [ProductController::class, 'toggleActive']);
            Route::patch('/{product}/toggle-stock', [ProductController::class, 'toggleStock']);

            // Product images
            Route::post('/{product}/images', [ProductController::class, 'storeImage']);
            Route::post('/{product}/images/{image}', [ProductController::class, 'updateImage']);
            Route::delete('/{product}/images/{image}', [ProductController::class, 'destroyImage']);
        });

        Route::prefix('sections')->group(function () {
            Route::get('/', [SectionController::class, 'index']);
            Route::get('/{section}', [SectionController::class, 'show']);
            Route::post('/{section}/products', [SectionController::class, 'assignProduct']);
            Route::patch('/{section}/products/{id}', [SectionController::class, 'updateProduct']);
            Route::delete('/{section}/products/{id}', [SectionController::class, 'removeProduct']);
        });

        Route::prefix('hero-banners')->group(function () {
            Route::get('/', [HeroBannerController::class, 'index']);
            Route::post('/', [HeroBannerController::class, 'store']);
            Route::get('/{heroBanner}', [HeroBannerController::class, 'show']);
            Route::post('/{heroBanner}', [HeroBannerController::class, 'update']);
            Route::delete('/{heroBanner}', [HeroBannerController::class, 'destroy']);
            Route::patch('/{heroBanner}/toggle-active', [HeroBannerController::class, 'toggleActive']);
        });

    });
});
