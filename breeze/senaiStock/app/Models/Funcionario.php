<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'Id_funcionario';

    public function cargo()
    {
        return $this->belongsTo(
            Cargo::class,
            'Id_cargo_FK', // FK na tabela funcionarios
            'Id_cargo'     // PK na tabela cargos
        );
    }

    public function requisicoes()
{
    return $this->hasMany(Requisicao::class);
}
}