<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Funcionario extends Model
{
    use HasFactory;

    protected $primaryKey = 'Id_funcionario';
    public $timestamps = false;

    protected $fillable = ['NIF', 'Nome', 'Cpf', 'Id_cargo_FK'];

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(
            Cargo::class,
            'Id_cargo_FK',
            'Id_cargo'
        );
    }
}
