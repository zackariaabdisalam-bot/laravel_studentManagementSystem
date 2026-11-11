<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date:Y-m-d',
        ];
    }

    // Student belongs to a course
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // Student has many attendance records
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // Student has many fees
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    // Student has many payments
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Student has many enrollments
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}

