<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Livro extends Model
{
    protected $fillable = ['Isbn', 'Titulo', 'Categoria'];

    public function estoques() : HasMany {
        return $this->hasMany(Estoque::class, 'Id_livro_FK');
    }

    public function requisicoes()
{
    return $this->hasMany(Requisicao::class);
}
}