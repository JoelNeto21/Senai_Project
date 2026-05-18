<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\MovementController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes - Protected by Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Books API Resource
    Route::apiResource('books', BookController::class);
    
    // Movements API
    Route::post('movements', [MovementController::class, 'store']);
});
