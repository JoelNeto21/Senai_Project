<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Curso;


class Turma extends Model
{
    protected $fillable = ["nome_turma", "curso_id"];

    public function curso() : BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
}
