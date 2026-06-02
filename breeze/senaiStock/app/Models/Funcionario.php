<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Funcionario extends Model
{
    use HasFactory;

    protected $primaryKey = 'Id_funcionario';
    public $timestamps = false;

    protected $fillable = ['NIF', 'Nome', 'Cpf', 'password', 'Id_cargo_FK'];

    protected $hidden = ['password'];

    /**
     * Set the funcionario's password.
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(
            Cargo::class,
            'Id_cargo_FK',
            'Id_cargo'
        );
    }
}