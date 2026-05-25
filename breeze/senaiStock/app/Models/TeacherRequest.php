<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_name',
        'teacher_email',
        'class_name',
        'subject',
        'book_id',
        'title',
        'quantity',
        'status',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'book_id' => 'integer',
        'quantity' => 'integer',
        'due_date' => 'date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
