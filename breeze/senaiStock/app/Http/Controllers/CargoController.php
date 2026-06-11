<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
class CargoController extends Controller
{
    public function index()
    {
        $cargos = Cargo::all();
        $viewPath = view()->exists('cargos.index') ? 'cargos.index' : 'temp_cargos_index';

        return view($viewPath, compact('cargos'));
    }
}
