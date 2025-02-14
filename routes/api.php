<?php

use App\Http\Controllers\AppPositionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/


Route::get('/appTopCategory', [AppPositionController::class, 'index']);

