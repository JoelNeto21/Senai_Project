<?php

use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\SenaiStockController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\CargoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    return $request->session()->has('employee.id')
        ? redirect()->route('senai.dashboard')
        : redirect()->route('employee.login');
});

Route::get('/entrada', [EmployeeAuthController::class, 'create'])->name('employee.login');
Route::post('/entrada', [EmployeeAuthController::class, 'store'])->name('employee.authenticate');
Route::post('/sair', [EmployeeAuthController::class, 'destroy'])->name('employee.logout');

Route::middleware('employee.auth')->group(function () {
    Route::get('/dashboard/{view?}', [SenaiStockController::class, 'index'])
        ->whereIn('view', ['insights', 'overview', 'teacher_requests', 'purchases', 'history', 'dashboard', 'library', 'receive', 'withdraw'])
        ->name('senai.dashboard');
    
    // Funcionarios routes
    Route::resource('funcionarios', FuncionarioController::class);
    
    // Cargos routes
    Route::resource('cargos', CargoController::class)->only(['index', 'store', 'destroy']);
});

require __DIR__.'/auth.php';
