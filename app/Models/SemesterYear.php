<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SemesterYear extends Model
{
    use HasFactory;

    protected $table = 'semesters_years';

    protected $fillable = [
        'name',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'semester_id');
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'semester_id');
    }
}
