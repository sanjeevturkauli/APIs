<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MpinController;

Route::get('/test', function (Request $request) {
    return response()->json([
        'status'=>'success',
        'statusCode'=>200,
        'message'=>"API successfuly tested Latest ok."
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('user')->middleware(['api.auth'])->group(function (): void {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('mpin')->group(function () {
        Route::post('/', [MpinController::class, 'store']);
        Route::put('/', [MpinController::class, 'update']);
        Route::delete('/', [MpinController::class, 'destroy']);
        Route::post('/verify', [MpinController::class, 'verify']);
    });
});


