<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_id',
        'fee_structure_id',
        'amount',
        'paid_at',
        'method',
        'reference',
        'mpesa_phone',
        'transaction_reference',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'receipt_number',
        'status',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    // Payment belongs to a student
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // Payment belongs to a fee record
    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }

    // Payment belongs to a fee structure
    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    // Payment belongs to the user who received it
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
