<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
     'fee_id',
     'student_id',
     'amount',
     'paid_at',
     'method', 
     'reference',
     'received_by',
     ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2',
         'paid_at' => 'date'];
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
