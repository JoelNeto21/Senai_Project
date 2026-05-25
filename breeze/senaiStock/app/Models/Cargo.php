<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    protected $primaryKey = 'Id_cargo';
    public $timestamps = false;

    protected $fillable = ['Nome_cargo'];

    public function funcionarios()
    {
        return $this->hasMany(
            Funcionario::class,
            'Id_cargo_FK',
            'Id_cargo'
        );
    }
}
