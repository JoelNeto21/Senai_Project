<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = ['nome_curso'];

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class);
    }
}
