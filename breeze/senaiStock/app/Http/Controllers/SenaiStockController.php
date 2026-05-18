<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Funcionario;
use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SenaiStockController extends Controller
{
    private const VALID_VIEWS = [
        'insights',
        'overview',
        'teacher_requests',
        'purchases',
        'history',
        'dashboard',
        'library',
        'receive',
        'withdraw',
    ];

    public function index(Request $request, string $view = 'insights'): View
    {
        if (!in_array($view, self::VALID_VIEWS, true)) {
            abort(404);
        }

        $books = collect(config('senaistock.books', []));
        $purchaseOrders = collect(config('senaistock.purchase_orders', []));
        $teacherRequests = collect(config('senaistock.teacher_requests', []));
        $navigationItems = config('senaistock.navigation_items', []);
        $employee = $request->session()->get('employee', []);
        $turmas = Turma::with('curso')->orderBy('nome_turma')->get();
        $cargos = Cargo::orderBy('Nome_cargo')->get();
        $funcionarios = Funcionario::with('cargo')->orderBy('Nome')->get();

        return view('senai-stock.index', [
            'activeView' => $view,
            'navigationItems' => $navigationItems,
            'employee' => $employee,
            'books' => $books,
            'purchaseOrders' => $purchaseOrders,
            'teacherRequests' => $teacherRequests,
            'turmas' => $turmas,
            'cargos' => $cargos,
            'funcionarios' => $funcionarios,
            'stockCriticalThreshold' => 8,
            'lowStockCount' => $books->where('quantity', '<', 8)->count(),
            'totalQuantity' => $books->sum('quantity'),
            'pendingTeacherRequests' => $teacherRequests->where('status', 'pendente')->count(),
            'purchaseCartCount' => 0,
            'withdrawCartCount' => 0,
        ]);
    }
}