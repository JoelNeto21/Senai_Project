<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Turma;

class Curso extends Model
{
    protected $fillable = ["nome_curso"];

    public function turmas() : HasMany
    {
        return $this->hasMany(Turma::class);
    }
}
