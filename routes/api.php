<?php



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Sport\SportController;
use App\Http\Controllers\Summary\SummaryController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Athlete\AthleteController;
use App\Http\Controllers\GroupChat\GroupChatController;
use App\Http\Controllers\Performance\PerformanceCategoryController;

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
        Route::post('/sports-edit', [SportController::class, 'edit']);

        Route::post('/users', [AccountController::class, 'createUser']);

        // summay
        Route::get('/summary',[SummaryController::class, 'summary']);
    });

    Route::prefix('athlete')->group(function () {
         // summay
        Route::get('/summary-athlete',[SummaryController::class, 'summaryAthelete']);

        Route::post('/assign-sport',[SportController::class,'assignAthlete']);

        Route::get('/sport/{id}', [SportController::class, 'getSportById']);

        Route::post('/logout', [AuthController::class, 'logout']);
        // Add other athlete routes here
    });

    Route::prefix('coach')->group(function () {
        // summay
        Route::get('/summary-coach',[SummaryController::class, 'summaryCoach']);

        Route::get('/sport/{id}', [SportController::class, 'getSportById']);

        Route::post('/logout', [AuthController::class, 'logout']);
        // Add other coach routes here
    });

    Route::prefix('profile')->group(function(){
        Route::post('/update', [AuthController::class, 'update']);
    });

    Route::prefix('communication')->group(function(){
        Route::get('/groupchats', [GroupChatController::class, 'index']); // Get all group chats of user
        Route::get('/groupusers/{id}', [GroupChatController::class, 'users']); // Get all group chats of user
        Route::get('/groupchats/{id}', [GroupChatController::class, 'show']); // Get chat messages
        Route::post('/groupchats/{id}/send', [GroupChatController::class, 'sendMessage']); // Send message
    });

    Route::prefix('performance')->group(function(){
        Route::post('/category/create', [PerformanceCategoryController::class, 'store']);
        Route::post('/category/update', [PerformanceCategoryController::class, 'update']);
        Route::get('/categories/{sport_id}', [PerformanceCategoryController::class, 'sportCategories']);
        Route::post('/category/insert', [PerformanceCategoryController::class, 'sportCategoryValue']);
        Route::post('/category/edit', [PerformanceCategoryController::class, 'sportCategoryValueEdit']);
    });

    Route::prefix('linear')->group(function(){
        Route::get('performance/chart/{sport_id}',[PerformanceCategoryController::class, 'chartData']);
        Route::get('performance/chart-athlete/{sport_id}',[PerformanceCategoryController::class, 'chartDataAthlete']);
        Route::post('/athlete/predict', [AthleteController::class, 'predictPerformance']);
    });
});

Route::options('{any}', function () {
    return response()->json(['status' => 'OK'], 200, [
        'Access-Control-Allow-Origin' => 'http://localhost:5173',
        'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        'Access-Control-Allow-Credentials' => 'true',
    ]);
})->where('any', '.*');


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


// testing for analysis
Route::get('performance/analysis/{sport_id}',[PerformanceCategoryController::class, 'analyzePerformance']);
Route::get('performance/analysis/single/{sport_id}',[PerformanceCategoryController::class, 'getSingleAthletePerformance']);
