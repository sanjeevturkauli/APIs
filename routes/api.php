<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function (Request $request) {
    return response()->json([
        'status'=>'success',
        'statusCode'=>200,
        'message'=>"API successfuly tested ok."
    ]);
});
