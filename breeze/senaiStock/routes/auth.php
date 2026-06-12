<?php

use App\Http\Controllers\EmployeeRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('register', [EmployeeRegistrationController::class, 'create'])
    ->name('register');

Route::post('register', [EmployeeRegistrationController::class, 'store']);
