<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Auth\AuthController;
// Route::get('/user-public', function (Request $request) {
//     $users = User::get();
//     return $users;
// });

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

