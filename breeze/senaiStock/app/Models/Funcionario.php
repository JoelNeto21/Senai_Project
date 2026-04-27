<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $guarded = [];

    public function cargo()
    {
        // Se a coluna na migration for 'cargo_id', use apenas:
        // return $this->belongsTo(Cargo::class);

        // Se manteve 'Id_cargo_FK', use assim:
        return $this->belongsTo(Cargo::class, 'Id_cargo_FK');
    }
}
