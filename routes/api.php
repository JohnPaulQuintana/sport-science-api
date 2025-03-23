<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Sport\SportController;

// Route::get('/user-public', function (Request $request) {
//     $users = User::get();
//     return $users;
// });

// Authentication Routes

Route::get('/test', [AuthController::class, 'api_test']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        // Admin creates a sport
        Route::post('/sports', [SportController::class, 'store'])->middleware('auth:admin');
    });

    Route::prefix('athlete')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        // Add other athlete routes here
    });

    Route::prefix('coach')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        // Add other coach routes here
    });
});


