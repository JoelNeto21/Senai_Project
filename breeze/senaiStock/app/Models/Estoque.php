<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Turma;

class Livro extends Model
{
    protected $fillable = ['Quantidade'];

    public function estoque() : HasMany{
        return $this->belongsTo(Estoque::class);
    }
}
