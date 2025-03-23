<?php



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Sport\SportController;
use App\Http\Controllers\Summary\SummaryController;
use App\Http\Controllers\Account\AccountController;
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
        Route::post('/sports', [SportController::class, 'store']);
        Route::post('/users', [AccountController::class, 'createUser']);

        // summay
        Route::get('/summary',[SummaryController::class, 'summary']);
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

Route::get('/storage/{path}', function ($path) {
    $file = storage_path('app/public/' . $path);

    if (!file_exists($file)) {
        return response()->json([
            'error' => 'File not found',
            'path' => $file,
            'exists' => file_exists($file) ? 'Yes' : 'No'
        ], 404);
    }

    return response()->file($file, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
    ]);
})->where('path', '.*');


