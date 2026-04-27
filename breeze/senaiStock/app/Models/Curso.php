<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Turma;

class Curso extends Model
{
    protected $fillable = ["nome_curso"];

    public function turma() : BelongsTo{
        return $this->belongsTo(Turma::class);
    }
}
