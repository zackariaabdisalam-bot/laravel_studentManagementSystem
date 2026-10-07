<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $studentTableExists = Schema::hasTable('students');
        $courseTableExists = Schema::hasTable('courses');
        $enrollmentTableExists = Schema::hasTable('enrollments');
        $feeStructureTableExists = Schema::hasTable('fee_structures');
        $paymentsTableExists = Schema::hasTable('payments');

        $feesCollected = $paymentsTableExists ? Payment::query()->sum('amount') : 0;

        $outstandingFees = 0;
        $courses = collect();

        if ($feeStructureTableExists && $paymentsTableExists) {
            $outstandingFees = FeeStructure::query()
                ->selectRaw('COALESCE(SUM(amount), 0) - COALESCE((SELECT SUM(payments.amount) FROM payments WHERE payments.fee_structure_id = fee_structures.id), 0) AS outstanding')
                ->value('outstanding');
        }

        if ($courseTableExists) {
            $courseQuery = Course::query();
            $courseNameColumn = Schema::hasColumn('courses', 'course_name')
                ? 'course_name'
                : (Schema::hasColumn('courses', 'name') ? 'name' : 'id');

            if ($enrollmentTableExists) {
                $courseQuery->withCount('students');
            } else {
                $courseQuery->select('courses.*')->selectRaw('0 AS students_count');
            }

            $courses = $courseQuery
                ->orderByDesc('students_count')
                ->orderBy($courseNameColumn)
                ->take(5)
                ->get();
        }

        return view('dashboard', [
            'totalStudents' => $studentTableExists ? Student::query()->count() : 0,
            'totalCourses' => $courseTableExists ? Course::query()->count() : 0,
            'feesCollected' => (float) $feesCollected,
            'outstandingFees' => (float) $outstandingFees,
            'recentStudents' => $studentTableExists
                ? Student::query()->latest('id')->take(5)->get()
                : collect(),
            'recentPayments' => $paymentsTableExists
                ? Payment::query()->with(['student', 'feeStructure'])->latest('id')->take(5)->get()
                : collect(),
            'courses' => $courses,
        ]);
    }
}
