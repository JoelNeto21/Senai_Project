<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{

    public function index(){
    $cargos = Cargo::all(); // Busca todos os cargos no banco
    // Temporarily use temp file until directories are properly created
    $viewPath = 'temp_cargos_index';
    if (view()->exists('cargos.index')) {
        $viewPath = 'cargos.index';
    }
    return view($viewPath, compact('cargos'));
}

public function store(Request $request){
    // Validação simples
    $request->validate([
        'Nome_cargo' => 'required|unique:cargos,Nome_cargo|max:255',
    ]);

    Cargo::create([
        'Nome_cargo' => $request->Nome_cargo,
    ]);

    return redirect()->route('cargos.index')->with('success', 'Cargo criado com sucesso!');
}

public function destroy(Cargo $cargo){
    $cargo->delete();
    return redirect()->route('cargos.index')->with('success', 'Cargo removido.');
}
}
