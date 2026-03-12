<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\V1\CategoryController;
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
    // Public category routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // Protected category routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::post('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });
});
