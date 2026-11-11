@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <p class="text-uppercase text-primary fw-semibold small mb-1">School overview</p>
                <h1 class="h2 fw-bold mb-1">Dashboard</h1>
                <p class="text-secondary mb-0">A real-time view of students, enrollment, and fee collection.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('students.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-people me-1" aria-hidden="true"></i>Students
                </a>
                <a href="{{ route('payments.index') }}" class="btn btn-primary">
                    <i class="bi bi-credit-card me-1" aria-hidden="true"></i>Payments
                </a>
            </div>
        </div>

        <section class="dashboard-welcome card border-0 shadow-sm text-white p-4 p-lg-5 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge rounded-pill bg-white bg-opacity-25 text-white mb-3">ADMINISTRATION</span>
                    <h2 class="h3 fw-bold mb-2">Welcome{{ auth()->user()?->name ? ', '.auth()->user()->name : '' }}</h2>
                    <p class="mb-0 opacity-75">Monitor school performance and keep every student payment accounted for.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="small opacity-75">Today</div>
                    <div class="h4 fw-semibold mb-0">{{ now()->format('l, j F Y') }}</div>
                </div>
            </div>
        </section>

        <section class="row g-3 g-xl-4 mb-4 dashboard-stats" aria-label="School summary">
            <div class="col-6 col-xl-3">
                <x-summary-card title="total students" :value="number_format($totalStudents)" icon="bi-people-fill" />
            </div>
            <div class="col-6 col-xl-3">
                <x-summary-card title="total courses" :value="number_format($totalCourses)" icon="bi-book-fill" />
            </div>
            <div class="col-6 col-xl-3">
                <x-summary-card title="fees collected" :value="'KSh '.number_format((float) $feesCollected, 2)" icon="bi-cash-stack" />
            </div>
            <div class="col-6 col-xl-3">
                <x-summary-card title="outstanding fees" :value="'KSh '.number_format((float) $outstandingFees, 2)" icon="bi-wallet2" />
            </div>
        </section>

        <section class="row g-3 g-xl-4 mb-4" aria-label="School charts">
            <div class="col-12 col-xl-8">
                <article class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h2 class="h5 fw-bold mb-1">Fee collection trend</h2>
                                <p class="small text-secondary mb-0">Completed payments over the last six months</p>
                            </div>
                            <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis">Verified payments</span>
                        </div>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="dashboard-chart-wrap">
                            <canvas id="paymentTrendChart" role="img" aria-label="Line chart showing completed fee payments for the last six months"></canvas>
                            <p class="chart-empty d-none">No completed payments in this period.</p>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-xl-4">
                <article class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h2 class="h5 fw-bold mb-1">Payment methods</h2>
                        <p class="small text-secondary mb-0">Collected amount by method</p>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                            <canvas id="paymentMethodsChart" role="img" aria-label="Doughnut chart showing completed payments by method"></canvas>
                            <p class="chart-empty d-none">No completed payments recorded yet.</p>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-xl-7">
                <article class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h2 class="h5 fw-bold mb-1">Enrollment by course</h2>
                        <p class="small text-secondary mb-0">Students with an enrollment in each course</p>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="dashboard-chart-wrap">
                            <canvas id="courseEnrollmentsChart" role="img" aria-label="Bar chart showing student enrollment by course"></canvas>
                            <p class="chart-empty d-none">No course enrollments recorded yet.</p>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-12 col-xl-5">
                <article class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h2 class="h5 fw-bold mb-1">Student status</h2>
                        <p class="small text-secondary mb-0">Current student records by status</p>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                            <canvas id="studentStatusesChart" role="img" aria-label="Doughnut chart showing the number of students by status"></canvas>
                            <p class="chart-empty d-none">No student records available yet.</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section class="row g-3 g-xl-4">
            <div class="col-12 col-xl-6">
                <article class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Recently added students</h2>
                            <p class="small text-secondary mb-0">The latest student records</p>
                        </div>
                        <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="small text-secondary">
                                    <th class="px-4 py-3 fw-semibold">Student</th>
                                    <th class="py-3 fw-semibold">Course</th>
                                    <th class="py-3 pe-4 fw-semibold text-end">Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentStudents as $student)
                                    <tr>
                                        <td class="px-4">
                                            <div class="fw-semibold">{{ trim($student->first_name.' '.$student->last_name) }}</div>
                                            <div class="small text-secondary">{{ $student->student_number }}</div>
                                        </td>
                                        <td>{{ $student->enrollments->first()?->course?->name ?? 'Unassigned' }}</td>
                                        <td class="pe-4 text-end text-nowrap">{{ $student->created_at?->format('M j, Y') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-secondary py-5">No students have been added yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <div class="col-12 col-xl-6">
                <article class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Recent payments</h2>
                            <p class="small text-secondary mb-0">Latest completed fee payments</p>
                        </div>
                        <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="small text-secondary">
                                    <th class="px-4 py-3 fw-semibold">Student</th>
                                    <th class="py-3 fw-semibold">Fee</th>
                                    <th class="py-3 fw-semibold">Date</th>
                                    <th class="py-3 pe-4 fw-semibold text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPayments as $payment)
                                    <tr>
                                        <td class="px-4 fw-semibold">{{ trim(($payment->student?->first_name ?? '').' '.($payment->student?->last_name ?? '')) ?: 'Student unavailable' }}</td>
                                        <td>{{ $payment->fee?->title ?? 'Fee unavailable' }}</td>
                                        <td class="text-nowrap">{{ ($payment->paid_at ?? $payment->created_at)?->format('M j, Y') ?? '—' }}</td>
                                        <td class="pe-4 text-end text-nowrap fw-semibold">KSh {{ number_format((float) $payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-secondary py-5">No completed payments have been recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .dashboard-welcome {
            background: linear-gradient(115deg, #1d4ed8 0%, #312e81 58%, #172554 100%);
            overflow: hidden;
            position: relative;
        }

        .dashboard-welcome::after {
            background: radial-gradient(circle, rgb(255 255 255 / 13%) 0, rgb(255 255 255 / 0%) 70%);
            content: "";
            height: 22rem;
            position: absolute;
            right: -7rem;
            top: -11rem;
            width: 22rem;
            pointer-events: none;
        }

        .dashboard-chart-wrap {
            height: 300px;
            position: relative;
        }

        .dashboard-chart-wrap--compact {
            height: 260px;
        }

        .chart-empty {
            align-items: center;
            color: #64748b;
            display: flex;
            inset: 0;
            justify-content: center;
            margin: 0;
            position: absolute;
            text-align: center;
        }

        @media (max-width: 575.98px) {
            .dashboard-chart-wrap {
                height: 250px;
            }

            .dashboard-chart-wrap--compact {
                height: 230px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const data = {{ \Illuminate\Support\Js::from($chartData) }};
            const palette = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
            const money = new Intl.NumberFormat('en-KE', {
                style: 'currency',
                currency: 'KES',
                maximumFractionDigits: 0
            });

            const showEmptyState = (canvas, values) => {
                if (values.some((value) => Number(value) > 0)) {
                    return false;
                }

                canvas.classList.add('d-none');
                canvas.parentElement.querySelector('.chart-empty')?.classList.remove('d-none');
                return true;
            };

            const lineCanvas = document.getElementById('paymentTrendChart');
            if (!showEmptyState(lineCanvas, data.paymentTrend.values)) {
                new Chart(lineCanvas, {
                    type: 'line',
                    data: {
                        labels: data.paymentTrend.labels,
                        datasets: [{
                            label: 'Fees collected',
                            data: data.paymentTrend.values,
                            borderColor: palette[0],
                            backgroundColor: 'rgba(37, 99, 235, 0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (context) => money.format(context.raw) } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: (value) => money.format(value) },
                                grid: { color: 'rgba(148, 163, 184, 0.18)' }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            const addDoughnutChart = (id, chart, formatValue) => {
                const canvas = document.getElementById(id);
                if (showEmptyState(canvas, chart.values)) {
                    return;
                }

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: chart.labels,
                        datasets: [{
                            data: chart.values,
                            backgroundColor: palette,
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        cutout: '68%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18 } },
                            tooltip: { callbacks: { label: (context) => `${context.label}: ${formatValue(context.raw)}` } }
                        }
                    }
                });
            };

            addDoughnutChart('paymentMethodsChart', data.paymentMethods, (value) => money.format(value));
            addDoughnutChart('studentStatusesChart', data.studentStatuses, (value) => Number(value).toLocaleString());

            const courseCanvas = document.getElementById('courseEnrollmentsChart');
            if (!showEmptyState(courseCanvas, data.courseEnrollments.values)) {
                new Chart(courseCanvas, {
                    type: 'bar',
                    data: {
                        labels: data.courseEnrollments.labels,
                        datasets: [{
                            label: 'Students',
                            data: data.courseEnrollments.values,
                            backgroundColor: palette[0],
                            borderRadius: 7,
                            maxBarThickness: 38
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148, 163, 184, 0.18)' } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }
        });
    </script>
@endpush
