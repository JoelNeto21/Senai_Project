<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{

    public function index(){
    $cargos = Cargo::all(); 
    return view('cargos.index', compact('cargos'));
}

public function store(Request $request){
    // Validação simples
    $request->validate([
        'Nome_cargo' => 'required|unique:cargos|max:255',
    ]);

    Role::create([
        'Nome_cargo' => $request->nome_cargo,
    ]);

    return redirect()->route('cargos.index')->with('success', 'Cargo criado com sucesso!');
}

public function destroy(Role $cargo){
    $cargo->delete();
    return redirect()->route('cargos.index');
}
}
