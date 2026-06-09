<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol',
        'requested_by_funcionario_id',
        'teacher_name',
        'teacher_email',
        'class_name',
        'course_name',
        'subject',
        'book_id',
        'title',
        'quantity',
        'fulfilled_quantity',
        'status',
        'due_date',
        'notes',
        'approved_at',
        'prepared_at',
        'rejected_at',
        'notified_at',
    ];

    protected $casts = [
        'book_id' => 'integer',
        'requested_by_funcionario_id' => 'integer',
        'quantity' => 'integer',
        'fulfilled_quantity' => 'integer',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'prepared_at' => 'datetime',
        'rejected_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'requested_by_funcionario_id', 'Id_funcionario');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TeacherRequestMessage::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(StockNotification::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pendente';
    }
}
