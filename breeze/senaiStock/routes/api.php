<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\MovementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'employee.auth', 'employee.role:Coordenador'])->group(function () {
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
