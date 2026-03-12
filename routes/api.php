<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\V1\CategoryController;
use App\Http\Controllers\V1\ProductController;
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

    });
});
