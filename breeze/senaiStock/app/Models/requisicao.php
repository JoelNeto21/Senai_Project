<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisicao extends Model
{
    protected $fillable = [
        'data_requisicao',
        'livro_id',
        'turma_id',
        'funcionario_id',
        'nome_turma',
        'titulo_livro',
        'quantidade_livro',
    ];

    // RELACIONAMENTOS

    public function livro()
    {
        return $this->belongsTo(Livro::class);
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
