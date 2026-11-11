<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaPaymentRequest extends Model
{
    use HasFactory;

    protected $table = 'mpesa_payment_requests';

    protected $fillable = [
        'fee_id',
        'payment_id',
        'amount',
        'phone',
        'account_reference',
        'merchant_request_id',
        'checkout_request_id',
        'status',
        'result_description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * The fee associated with this M-Pesa request.
     */
    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }

    /**
     * The payment created after successful confirmation.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Check whether the request is still awaiting confirmation.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check whether Safaricom confirmed payment.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check whether the transaction failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
