<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_number',
        'firstname',
        'lastname',
        'gender',
        'email',
        'phone no',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return ['enrollment_date' => 'date'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
