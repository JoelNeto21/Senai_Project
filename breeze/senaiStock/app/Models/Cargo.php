<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'Id_cargo';

    public function funcionarios()
    {
        return $this->hasMany(
            Funcionario::class,
            'Id_cargo_FK', // FK em funcionarios
            'Id_cargo'     // PK em cargos
        );
    }
}