<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'subject',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all movements associated with this book.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * Check if book has critical stock (< 10 units).
     */
    public function isCriticalStock(): bool
    {
        return $this->quantity < 10;
    }

    /**
     * Check if book is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }
}
