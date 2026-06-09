<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'teacher_request_id',
        'book_id',
        'title',
        'quantity',
        'received_quantity',
        'unit_cost',
        'type',
        'justification',
    ];

    protected $casts = [
        'purchase_order_id' => 'integer',
        'teacher_request_id' => 'integer',
        'book_id' => 'integer',
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function teacherRequest(): BelongsTo
    {
        return $this->belongsTo(TeacherRequest::class);
    }
}
