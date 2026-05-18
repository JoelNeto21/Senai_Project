<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Funcionario extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'Id_funcionario';
    protected $fillable = ['NIF', 'Nome', 'Cpf', 'Id_cargo_FK'];

    public function cargo() : BelongsTo
    {
        return $this->belongsTo(
            Cargo::class,
            'Id_cargo_FK', // FK na tabela funcionarios
            'Id_cargo'     // PK na tabela cargos
        );
    }
}