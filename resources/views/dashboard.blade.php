@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid px-0">
        <h1 class="h3 fw-bold mb-3">Dashboard</h1>
        <section class="card border-0 shadow-sm text-white p-3 p-lg-4 mb-4"
                 style="background: linear-gradient(110deg, #1d4ed8 0%, #1e3a8a 100%);">
            <div>
                <h2 class="h6 fw-semibold mb-0">Welcome to the Student Management System</h2>
            </div>
        </section>

        <section class="row g-2 g-sm-3 g-xl-4 mb-4 dashboard-stats" aria-label="School summary">
            <div class="col-3">
                <x-summary-card title="total students" :value="number_format($totalStudents)" icon="bi-people-fill" />
            </div>
            <div class="col-3">
                <x-summary-card title="total courses" :value="number_format($totalCourses)" icon="bi-book-fill" />
            </div>
            <div class="col-3">
                <x-summary-card title="fees collected" :value="'ksh '.number_format((float) $feesCollected, 2)" icon="bi-cash-stack" />
            </div>
            <div class="col-3">
                <x-summary-card title="outstanding fees" :value="'ksh '.number_format((float) $outstandingFees, 2)" icon="bi-wallet2" />
            </div>
        </section>

        <div class="row g-3 g-xl-4">
            <div class="col-12 col-xl-7">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Recent students</h2>
                            <p class="small text-secondary mb-0">Latest enrollments</p>
                        </div>
                        <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View students</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="small text-secondary">
                                    <th class="px-4 py-3 fw-semibold">Student</th>
                                    <th class="py-3 fw-semibold">Course</th>
                                    <th class="py-3 pe-4 fw-semibold text-end">Enrolled</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentStudents as $student)
                                    <tr>
                                        <td class="px-4">
                                            <div class="fw-semibold">{{ $student->name }}</div>
                                            <div class="small text-secondary">{{ $student->admission_number }}</div>
                                        </td>
                                        <td>{{ $student->course?->name ?? 'Unassigned' }}</td>
                                        <td class="pe-4 text-end text-nowrap">{{ $student->enrollment_date->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-secondary py-5">No students have been enrolled yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-5">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Course enrollment</h2>
                            <p class="small text-secondary mb-0">Students by course</p>
                        </div>
                        <a href="{{ route('courses.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View courses</a>
                    </div>
                    <div class="card-body px-4 pt-3">
                        @forelse ($courses as $course)
                            @php($enrollmentPercent = $totalStudents > 0 ? min(100, (int) round($course->students_count / $totalStudents * 100)) : 0)
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                    <span class="fw-semibold text-truncate">{{ $course->name }}</span>
                                    <span class="small text-secondary text-nowrap">{{ number_format($course->students_count) }} {{ $course->students_count === 1 ? 'student' : 'students' }}</span>
                                </div>
                                <div class="progress" role="progressbar" aria-label="{{ $course->name }} enrollment" aria-valuenow="{{ $enrollmentPercent }}" aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
                                    <div class="progress-bar rounded-pill" style="width: {{ $enrollmentPercent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-secondary py-5 mb-0">No courses have been added yet.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-12">
                <section class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Recent payments</h2>
                            <p class="small text-secondary mb-0">Latest fee payments received</p>
                        </div>
                        <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View payments</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="small text-secondary">
                                    <th class="px-4 py-3 fw-semibold">Student</th>
                                    <th class="py-3 fw-semibold">Fee</th>
                                    <th class="py-3 fw-semibold">Date</th>
                                    <th class="py-3 fw-semibold">Method</th>
                                    <th class="py-3 pe-4 fw-semibold text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPayments as $payment)
                                    <tr>
                                        <td class="px-4 fw-semibold">{{ $payment->student->name }}</td>
                                        <td>{{ $payment->fee->title }}</td>
                                        <td class="text-nowrap">{{ $payment->paid_at->format('M j, Y') }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                        <td class="pe-4 text-end text-nowrap fw-semibold">KSh {{ number_format((float) $payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-secondary py-5">No payments have been recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
