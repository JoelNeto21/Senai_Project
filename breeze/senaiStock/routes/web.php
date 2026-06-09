<?php

use App\Http\Controllers\CargoController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SenaiStockController;
use App\Support\EmployeeRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return $request->session()->has('employee.id')
        ? redirect()->route('senai.dashboard', [
            'view' => EmployeeRole::defaultView($request->session()->get('employee.cargo')),
        ])
        : redirect()->route('employee.login');
});

Route::get('/entrada', [EmployeeAuthController::class, 'create'])->name('employee.login');
Route::post('/entrada', [EmployeeAuthController::class, 'store'])->name('employee.authenticate');
Route::post('/sair', [EmployeeAuthController::class, 'destroy'])->name('employee.logout');

Route::middleware('employee.auth')->group(function () {
    Route::get('/dashboard', [SenaiStockController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/{view}', [SenaiStockController::class, 'index'])
        ->whereIn('view', ['insights', 'overview', 'teacher_requests', 'purchases', 'history', 'dashboard', 'library', 'receive', 'withdraw', 'movements', 'stock', 'reports', 'alerts', 'suppliers', 'classes', 'people'])
        ->name('senai.dashboard');

    Route::post('/estoque/livros/{book}/receber', [SenaiStockController::class, 'receiveExisting'])
        ->name('stock.books.receive');
    Route::post('/estoque/livros/novo', [SenaiStockController::class, 'storeNewMaterial'])
        ->name('stock.books.store-new');
    Route::post('/estoque/retiradas/lote', [SenaiStockController::class, 'withdrawBatch'])
        ->name('stock.withdraw.batch');
    Route::post('/estoque/pedidos-professores/{teacherRequest}/separar', [SenaiStockController::class, 'fulfillTeacherRequest'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.teacher-requests.fulfill');
    Route::post('/estoque/pedidos-professores/{teacherRequest}/aprovar', [SenaiStockController::class, 'approveTeacherRequest'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.teacher-requests.approve');
    Route::post('/estoque/pedidos-professores/{teacherRequest}/rejeitar', [SenaiStockController::class, 'rejectTeacherRequest'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.teacher-requests.reject');
    Route::post('/estoque/pedidos-professores/{teacherRequest}/notificar', [SenaiStockController::class, 'notifyTeacherRequest'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.teacher-requests.notify');
    Route::post('/estoque/pedidos-professores/{teacherRequest}/comprar', [SenaiStockController::class, 'addTeacherRequestToPurchase'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.teacher-requests.purchase');
    Route::post('/estoque/pedidos-professores', [SenaiStockController::class, 'storeTeacherRequest'])
        ->name('stock.teacher-requests.store');
    Route::post('/estoque/compras/gerar', [SenaiStockController::class, 'generatePurchaseOrder'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.purchases.generate');
    Route::post('/estoque/compras/{purchaseOrder}/aprovar', [SenaiStockController::class, 'approvePurchaseOrder'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.purchases.approve');
    Route::post('/estoque/compras/{purchaseOrder}/entregar', [SenaiStockController::class, 'markPurchaseOrderDelivered'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.purchases.deliver');
    Route::post('/estoque/compras/{purchaseOrder}/itens/{purchaseOrderItem}/receber', [SenaiStockController::class, 'receivePurchaseOrderItem'])
        ->middleware('employee.role:Coordenador')
        ->name('stock.purchases.items.receive');
    Route::post('/estoque/alertas/livros/{book}/comprar', [SenaiStockController::class, 'addCriticalBookToCart'])
        ->name('stock.alerts.purchase');
    Route::post('/estoque/turmas', [SenaiStockController::class, 'storeTurma'])
        ->name('stock.classes.store');

    Route::middleware('employee.role:Coordenador')->group(function () {
        Route::resource('funcionarios', FuncionarioController::class);
        Route::resource('cargos', CargoController::class)->only(['index', 'store', 'destroy']);
    });

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

Route::get('/teste', function () {
    return view('senai-stock.index', [
        'activeView' => 'insights',
        'navigationItems' => [],
        'employee' => [],
        'books' => collect(),
        'purchaseOrders' => collect(),
        'purchaseCart' => collect(),
        'teacherRequests' => collect(),
        'turmas' => collect(),
        'cargos' => collect(),
        'funcionarios' => collect(),
        'suppliers' => collect(),
        'notifications' => collect(),
        'professorNotifications' => collect(),
        'movements' => collect(),
        'alerts' => collect(),
        'stockCriticalThreshold' => 8,
        'lowStockCount' => 0,
        'totalQuantity' => 0,
        'pendingTeacherRequests' => 0,
        'purchaseCartCount' => 0,
        'withdrawCartCount' => 0,
        'alertCount' => 0,
        'supplierCount' => 0,
    ]);
});

Route::resource('requisicoes', RequisicaoController::class)
    ->middleware('employee.auth');

require __DIR__.'/auth.php';
