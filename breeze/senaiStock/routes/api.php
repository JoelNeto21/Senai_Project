<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\MovementController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');

// API Routes - Protected
Route::middleware('auth')->group(function () {
    // Books API
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{id}', [BookController::class, 'show']);
    Route::post('books', [BookController::class, 'store']);
    Route::put('books/{id}', [BookController::class, 'update']);
    Route::delete('books/{id}', [BookController::class, 'destroy']);

    // Movements API
    Route::post('movements', [MovementController::class, 'store']);
    Route::get('movements', [MovementController::class, 'index']);
    Route::get('movements/{id}', [MovementController::class, 'show']);
});

