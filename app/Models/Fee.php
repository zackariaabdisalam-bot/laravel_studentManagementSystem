<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fee extends Model
{
    use HasFactory;

    protected $table = 'fees';

    protected $fillable = ['student_id', 'title', 'amount', 'due_date', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'due_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) ($this->payments_sum_amount ?? $this->payments()->sum('amount')));
    }
}
