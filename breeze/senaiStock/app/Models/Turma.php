<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Turma;


class Turma extends Model
{
    protected $fillable = ["nome_turma","nome_curso"];

    public function requisicoes()
{
    return $this->hasMany(Requisicao::class);
}
}
