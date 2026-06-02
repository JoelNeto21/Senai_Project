<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'severity',
        'title',
        'body',
        'action_url',
        'teacher_request_id',
        'book_id',
        'read_at',
    ];

    protected $casts = [
        'teacher_request_id' => 'integer',
        'book_id' => 'integer',
        'read_at' => 'datetime',
    ];

    public function teacherRequest(): BelongsTo
    {
        return $this->belongsTo(TeacherRequest::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function markAsRead(): void
    {
        $this->forceFill(['read_at' => now()])->save();
    }
}
