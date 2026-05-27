<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estoque extends Model
{
    protected $fillable = ['Quantidade', 'Id_livro_FK'];

    public function livro() : BelongsTo {
        return $this->belongsTo(Livro::class, 'Id_livro_FK');
    }
}