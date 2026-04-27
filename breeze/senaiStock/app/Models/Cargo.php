<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $guarded = [];

    public function funcionarios() {
        // Um cargo possui muitos funcionários
        // O segundo parâmetro é a chave estrangeira que você criou na migration de funcionários
        return $this->hasMany(Funcionario::class, 'Id_cargo_FK');
    }
}


