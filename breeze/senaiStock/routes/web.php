<?php

use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\SenaiStockController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    return $request->session()->has('employee.id')
        ? redirect()->route('dashboard')
        : redirect()->route('employee.login');
});

Route::get('/entrada', [EmployeeAuthController::class, 'create'])->name('employee.login');
Route::post('/entrada', [EmployeeAuthController::class, 'store'])->name('employee.authenticate');
Route::post('/sair', [EmployeeAuthController::class, 'destroy'])->name('employee.logout');

Route::middleware('employee.auth')->group(function () {
    Route::get('/dashboard', [SenaiStockController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/{view}', [SenaiStockController::class, 'index'])
        ->whereIn('view', ['insights', 'overview', 'teacher_requests', 'purchases', 'history', 'dashboard', 'library', 'receive', 'withdraw', 'movements', 'alerts', 'suppliers', 'classes', 'people', 'settings'])
        ->name('senai.dashboard');

    Route::post('/estoque/livros/{book}/receber', [SenaiStockController::class, 'receiveExisting'])
        ->name('stock.books.receive');
    Route::post('/estoque/livros/novo', [SenaiStockController::class, 'storeNewMaterial'])
        ->name('stock.books.store-new');
    Route::post('/estoque/retiradas/lote', [SenaiStockController::class, 'withdrawBatch'])
        ->name('stock.withdraw.batch');
    Route::post('/estoque/pedidos-professores/{teacherRequest}/separar', [SenaiStockController::class, 'fulfillTeacherRequest'])
        ->name('stock.teacher-requests.fulfill');
    Route::post('/estoque/pedidos-professores/{teacherRequest}/comprar', [SenaiStockController::class, 'addTeacherRequestToPurchase'])
        ->name('stock.teacher-requests.purchase');
    Route::post('/estoque/pedidos-professores', [SenaiStockController::class, 'storeTeacherRequest'])
        ->name('stock.teacher-requests.store');
    Route::post('/estoque/compras/gerar', [SenaiStockController::class, 'generatePurchaseOrder'])
        ->name('stock.purchases.generate');
    Route::post('/estoque/compras/{purchaseOrder}/entregar', [SenaiStockController::class, 'markPurchaseOrderDelivered'])
        ->name('stock.purchases.deliver');
    Route::post('/estoque/alertas/livros/{book}/comprar', [SenaiStockController::class, 'addCriticalBookToCart'])
        ->name('stock.alerts.purchase');
    Route::post('/estoque/fornecedores', [SenaiStockController::class, 'storeSupplier'])
        ->name('stock.suppliers.store');
    Route::patch('/estoque/fornecedores/{supplier}/status', [SenaiStockController::class, 'updateSupplierStatus'])
        ->name('stock.suppliers.status');
    
    // Funcionarios routes
    Route::resource('funcionarios', FuncionarioController::class);
    
    // Cargos routes
    Route::resource('cargos', CargoController::class)->only(['index', 'store', 'destroy']);

    Route::prefix('api')->group(function () {
        Route::post('books/{book}/receive', [SenaiStockController::class, 'receiveViaApi']);
        Route::post('books/{book}/withdraw', [SenaiStockController::class, 'withdrawViaApi']);
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/teste', function (){
    return view('dashboard');
});

Route::resource('requisicoes', RequisicaoController::class);

require __DIR__.'/auth.php';

