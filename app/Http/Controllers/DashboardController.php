<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Services\PaymentReportData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $studentTableExists = Schema::hasTable('students');
        $courseTableExists = Schema::hasTable('courses');
        $enrollmentTableExists = Schema::hasTable('enrollments');
        $feeTableExists = Schema::hasTable('fees');
        $paymentsTableExists = Schema::hasTable('payments');

        $totalStudents = $studentTableExists ? Student::query()->count() : 0;
        $totalCourses = $courseTableExists ? Course::query()->count() : 0;

        $completedPayments = Payment::query()
            ->when(
                $paymentsTableExists && Schema::hasColumn('payments', 'status'),
                fn ($query) => $query->where('status', 'completed')
            );

        $feesCollected = $paymentsTableExists ? (float) (clone $completedPayments)->sum('amount') : 0.0;
        $totalFees = $feeTableExists ? (float) Fee::query()->sum('amount') : 0.0;
        $outstandingFees = max(0, $totalFees - $feesCollected);

        $courses = collect();
        if ($courseTableExists) {
            $courseQuery = Course::query();

            if ($enrollmentTableExists) {
                $courseQuery->withCount('students');
            } else {
                $courseQuery->select('courses.*')->selectRaw('0 AS students_count');
            }

            $courses = $courseQuery
                ->orderByDesc('students_count')
                ->orderBy('course_name')
                ->take(6)
                ->get();
        }

        $recentStudents = collect();
        $studentStatusCounts = collect();

        if ($studentTableExists) {
            $recentStudentQuery = Student::query()->latest('id');
            if ($enrollmentTableExists) {
                $recentStudentQuery->with(['enrollments' => fn ($query) => $query->latest()->with('course')]);
            }
            $recentStudents = $recentStudentQuery->take(5)->get();

            if (Schema::hasColumn('students', 'status')) {
                $studentStatusCounts = Student::query()
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->orderBy('status')
                    ->get();
            }
        }

        $recentPayments = collect();
        $paymentMethodTotals = collect();
        $monthlyPaymentTotals = collect();

        if ($paymentsTableExists) {
            $recentPaymentQuery = Payment::query()->with(['student', 'fee'])->latest('id');
            if (Schema::hasColumn('payments', 'status')) {
                $recentPaymentQuery->where('status', 'completed');
            }
            $recentPayments = $recentPaymentQuery->take(5)->get();

            $methodQuery = Payment::query()
                ->selectRaw('method, SUM(amount) as total')
                ->groupBy('method');
            if (Schema::hasColumn('payments', 'status')) {
                $methodQuery->where('status', 'completed');
            }
            $paymentMethodTotals = $methodQuery->orderBy('method')->get();

            if (Schema::hasColumn('payments', 'paid_at')) {
                $monthStart = now()->startOfMonth()->subMonths(5);
                $dailyTotals = Payment::query()
                    ->selectRaw('paid_at, SUM(amount) as total')
                    ->whereNotNull('paid_at')
                    ->whereBetween('paid_at', [$monthStart->toDateString(), now()->toDateString()])
                    ->when(
                        Schema::hasColumn('payments', 'status'),
                        fn ($query) => $query->where('status', 'completed')
                    )
                    ->groupBy('paid_at')
                    ->get();

                $monthlyPaymentTotals = $dailyTotals
                    ->groupBy(fn ($row) => Carbon::parse($row->paid_at)->format('Y-m'))
                    ->map(fn ($rows) => (float) $rows->sum('total'));
            }
        }

        $months = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));
        $chartData = [
            'paymentTrend' => [
                'labels' => $months->map(fn (Carbon $month) => $month->format('M Y'))->values(),
                'values' => $months->map(
                    fn (Carbon $month) => (float) ($monthlyPaymentTotals[$month->format('Y-m')] ?? 0)
                )->values(),
            ],
            'paymentMethods' => [
                'labels' => $paymentMethodTotals->pluck('method')
                    ->map(fn ($method) => PaymentReportData::METHODS[$method] ?? ucfirst(str_replace('_', ' ', $method)))
                    ->values(),
                'values' => $paymentMethodTotals->pluck('total')->map(fn ($total) => (float) $total)->values(),
            ],
            'courseEnrollments' => [
                'labels' => $courses->pluck('name')->values(),
                'values' => $courses->pluck('students_count')->map(fn ($total) => (int) $total)->values(),
            ],
            'studentStatuses' => [
                'labels' => $studentStatusCounts->pluck('status')->map(fn ($status) => ucfirst((string) $status))->values(),
                'values' => $studentStatusCounts->pluck('total')->map(fn ($total) => (int) $total)->values(),
            ],
        ];

        return view('dashboard', [
            'totalStudents' => $totalStudents,
            'totalCourses' => $totalCourses,
            'feesCollected' => $feesCollected,
            'outstandingFees' => $outstandingFees,
            'recentStudents' => $recentStudents,
            'recentPayments' => $recentPayments,
            'courses' => $courses,
            'chartData' => $chartData,
        ]);
    }
}
