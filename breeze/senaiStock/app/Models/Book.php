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
        'description',
        'pages',
        'publication_year',
        'image_path',
        'quantity',
        'minimum_stock',
        'location',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_stock' => 'integer',
        'pages' => 'integer',
        'publication_year' => 'integer',
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
        return $this->quantity < ($this->minimum_stock ?? 10);
    }

    /**
     * Check if book is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function isActive(): bool
    {
        return $this->status === 'ativo';
    }
}
