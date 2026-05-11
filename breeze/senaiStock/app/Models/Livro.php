<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Turma;

class Livro extends Model
{
    protected $fillable = ['Isbn', 'Titulo'];

public function estoque() {
    return $this->hasOne(Estoque::class, 'Id_livro_FK');
}
}
