<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'book_id',
        'user_id',
        'funcionario_id',
        'quantity',
        'justification',
    ];

    protected $casts = [
        'type' => 'string',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the book associated with this movement.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user associated with this movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the funcionario associated with this movement (if exists).
     */
    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'funcionario_id',
            'Id_funcionario'
        );
    }

    /**
     * Check if this is an entry movement (entrada).
     */
    public function isEntry(): bool
    {
        return $this->type === 'entrada';
    }

    /**
     * Check if this is an exit movement (saida).
     */
    public function isExit(): bool
    {
        return $this->type === 'saida';
    }
}
