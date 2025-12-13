<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function (Request $request) {
    return response()->json([
        'status'=>'success',
        'statusCode'=>200,
        'message'=>"API successfuly tested Latest ok."
    ]);
});

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['api.auth'])->group(function (): void {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
});


