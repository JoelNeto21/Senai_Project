<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Turma extends Model
{
    use HasFactory;

    protected $fillable = ['nome_turma', 'curso_id'];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
}
