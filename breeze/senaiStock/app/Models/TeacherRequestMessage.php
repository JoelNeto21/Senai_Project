<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherRequestMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_request_id',
        'funcionario_id',
        'sender_type',
        'sender_name',
        'status',
        'message',
        'email_sent',
        'sent_at',
    ];

    protected $casts = [
        'teacher_request_id' => 'integer',
        'funcionario_id' => 'integer',
        'email_sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function teacherRequest(): BelongsTo
    {
        return $this->belongsTo(TeacherRequest::class);
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id', 'Id_funcionario');
    }
}
