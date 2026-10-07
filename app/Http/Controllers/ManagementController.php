<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManagementController extends Controller
{
    private const MODULES = [
        'students' => [
            'title' => 'Students',
            'model' => Student::class,
            'with' => ['course'],
            'columns' => [
                'admission_number' => 'Admission number',
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'course.name' => 'Course',
                'enrollment_date' => 'Enrollment date',
                'status' => 'Status',
            ],
            'fields' => [
                ['name' => 'admission_number', 'label' => 'Admission number', 'type' => 'text', 'required' => true],
                ['name' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                ['name' => 'course_id', 'label' => 'Course', 'type' => 'select', 'options' => 'courses'],
                ['name' => 'enrollment_date', 'label' => 'Enrollment date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated'], 'required' => true],
            ],
        ],
        'courses' => [
            'title' => 'Courses',
            'model' => Course::class,
            'with' => [],
            'columns' => ['code' => 'Course code', 'name' => 'Course name', 'duration' => 'Duration'],
            'fields' => [
                ['name' => 'code', 'label' => 'Course code', 'type' => 'text', 'required' => true],
                ['name' => 'name', 'label' => 'Course name', 'type' => 'text', 'required' => true],
                ['name' => 'duration', 'label' => 'Duration', 'type' => 'text'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ],
        ],
        'attendance' => [
            'title' => 'Attendance',
            'model' => Attendance::class,
            'with' => ['student'],
            'columns' => [
                'student.admission_number' => 'Admission number',
                'student.name' => 'Student',
                'attendance_date' => 'Date',
                'status' => 'Status',
                'notes' => 'Notes',
            ],
            'fields' => [
                ['name' => 'student_id', 'label' => 'Student', 'type' => 'select', 'options' => 'students', 'required' => true],
                ['name' => 'attendance_date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'], 'required' => true],
                ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
            ],
        ],
        'fees' => [
            'title' => 'Fees',
            'model' => Fee::class,
            'with' => ['student'],
            'sum' => true,
            'columns' => [
                'student.admission_number' => 'Admission number',
                'student.name' => 'Student',
                'title' => 'Fee',
                'amount' => 'Amount (KSh)',
                'payments_sum_amount' => 'Paid (KSh)',
                'balance' => 'Balance (KSh)',
                'due_date' => 'Due date',
            ],
            'fields' => [
                ['name' => 'student_id', 'label' => 'Student', 'type' => 'select', 'options' => 'students', 'required' => true],
                ['name' => 'title', 'label' => 'Fee description', 'type' => 'text', 'required' => true],
                ['name' => 'amount', 'label' => 'Amount (KSh)', 'type' => 'number', 'required' => true, 'step' => '0.01'],
                ['name' => 'due_date', 'label' => 'Due date', 'type' => 'date'],
                ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
            ],
        ],
        'payments' => [
            'title' => 'Payments',
            'model' => Payment::class,
            'with' => ['student', 'fee'],
            'columns' => [
                'student.admission_number' => 'Admission number',
                'student.name' => 'Student',
                'fee.title' => 'Fee',
                'amount' => 'Amount (KSh)',
                'paid_at' => 'Date paid',
                'method' => 'Method',
                'reference' => 'Reference',
            ],
            'fields' => [
                ['name' => 'fee_id', 'label' => 'Fee record', 'type' => 'select', 'options' => 'fees', 'required' => true],
                ['name' => 'amount', 'label' => 'Amount (KSh)', 'type' => 'number', 'required' => true, 'step' => '0.01'],
                ['name' => 'paid_at', 'label' => 'Payment date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'method', 'label' => 'Payment method', 'type' => 'select', 'options' => ['cash' => 'Cash', 'mobile_money' => 'Mobile money', 'bank_transfer' => 'Bank transfer', 'card' => 'Card', 'other' => 'Other'], 'required' => true],
                ['name' => 'reference', 'label' => 'Reference (optional)', 'type' => 'text'],
            ],
        ],
    ];

    public function index(Request $request, string $module): View
    {
        abort_unless(isset(self::MODULES[$module]) || $module === 'reports', 404);

        if ($module === 'reports') {
            return $this->reports($request);
        }

        $definition = self::MODULES[$module];
        $query = $definition['model']::query()->with($definition['with']);

        if ($definition['sum'] ?? false) {
            $query->withSum('payments', 'amount');
        }

        $records = $query->latest('id')->paginate(15)->withQueryString();
        $editing = $request->integer('edit')
            ? $definition['model']::query()->findOrFail($request->integer('edit'))
            : null;
        $formValues = [];

        foreach ($definition['fields'] as &$field) {
            if (is_string($field['options'] ?? null)) {
                $field['choices'] = $this->options($field['options']);
            } else {
                $field['choices'] = $field['options'] ?? [];
            }

            $value = $editing ? data_get($editing, $field['name']) : null;
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d');
            } elseif ($value === null && ($field['default'] ?? null) === 'today') {
                $value = now()->toDateString();
            }
            $formValues[$field['name']] = $value ?? '';
        }
        unset($field);

        return view('management.index', [
            'module' => $module,
            'definition' => $definition,
            'records' => $records,
            'editing' => $editing,
            'formValues' => $formValues,
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $this->assertModule($module);
        $data = $request->validate($this->rules($module));
        $this->prepareData($module, $data);

        $model = self::MODULES[$module]['model'];
        $model::query()->create($data);

        return redirect()->route($module.'.index')->with('status', rtrim(self::MODULES[$module]['title'], 's').' record created successfully.');
    }

    public function update(Request $request, int $record, string $module): RedirectResponse
    {
        $this->assertModule($module);
        $model = self::MODULES[$module]['model'];
        $item = $model::query()->findOrFail($record);
        $data = $request->validate($this->rules($module, $item->getKey()));
        $this->prepareData($module, $data, $item);

        $item->update($data);

        return redirect()->route($module.'.index')->with('status', rtrim(self::MODULES[$module]['title'], 's').' record updated successfully.');
    }

    public function destroy(int $record, string $module): RedirectResponse
    {
        $this->assertModule($module);
        $model = self::MODULES[$module]['model'];
        $item = $model::query()->findOrFail($record);

        if ($module === 'students' && ($item->attendanceRecords()->exists() || $item->fees()->exists())) {
            return redirect()->route($module.'.index')->withErrors(['record' => 'Students with attendance or fee records cannot be deleted.']);
        }

        if ($module === 'courses' && $item->students()->exists()) {
            return redirect()->route($module.'.index')->withErrors(['record' => 'Courses assigned to students cannot be deleted. Reassign those students first.']);
        }

        if ($module === 'fees' && $item->payments()->exists()) {
            return redirect()->route($module.'.index')->withErrors(['record' => 'Fee records with payments cannot be deleted.']);
        }

        $item->delete();

        return redirect()->route($module.'.index')->with('status', rtrim(self::MODULES[$module]['title'], 's').' record deleted successfully.');
    }

    private function rules(string $module, ?int $record = null): array
    {
        $unique = fn (string $table, string $column) => Rule::unique($table, $column)->ignore($record);

        return match ($module) {
            'students' => [
                'admission_number' => ['required', 'string', 'max:30', $unique('students', 'admission_number')],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255', $unique('students', 'email')],
                'phone' => ['nullable', 'string', 'max:30'],
                'course_id' => ['nullable', 'exists:courses,id'],
                'enrollment_date' => ['required', 'date'],
                'status' => ['required', Rule::in(['active', 'inactive', 'graduated'])],
            ],
            'courses' => [
                'code' => ['required', 'string', 'max:30', $unique('courses', 'code')],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:5000'],
                'duration' => ['nullable', 'string', 'max:100'],
            ],
            'attendance' => [
                'student_id' => ['required', 'exists:students,id'],
                'attendance_date' => [
                    'required',
                    'date',
                    function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                        $duplicate = Attendance::query()
                            ->where('student_id', request()->input('student_id'))
                            ->whereDate('attendance_date', $value)
                            ->when($record, fn (Builder $query) => $query->whereKeyNot($record))
                            ->exists();

                        if ($duplicate) {
                            $fail('Attendance has already been recorded for this student on this date.');
                        }
                    },
                ],
                'status' => ['required', Rule::in(['present', 'absent', 'late', 'excused'])],
                'notes' => ['nullable', 'string', 'max:5000'],
            ],
            'fees' => [
                'student_id' => ['required', 'exists:students,id'],
                'title' => ['required', 'string', 'max:255'],
                'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
                'due_date' => ['nullable', 'date'],
                'notes' => ['nullable', 'string', 'max:5000'],
            ],
            'payments' => [
                'fee_id' => ['required', 'exists:fees,id'],
                'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
                'paid_at' => ['required', 'date'],
                'method' => ['required', Rule::in(['cash', 'mobile_money', 'bank_transfer', 'card', 'other'])],
                'reference' => ['nullable', 'string', 'max:255', $unique('payments', 'reference')],
            ],
        };
    }

    private function prepareData(string $module, array &$data, Student|Payment|null $item = null): void
    {
        if ($module === 'payments') {
            $fee = Fee::query()->withSum('payments', 'amount')->findOrFail($data['fee_id']);
            $alreadyPaid = (float) ($fee->payments_sum_amount ?? 0);

            if ($item instanceof Payment && $item->fee_id === $fee->id) {
                $alreadyPaid -= (float) $item->amount;
            }

            if ($alreadyPaid + (float) $data['amount'] > (float) $fee->amount + 0.00001) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment amount exceeds the outstanding balance for this fee.',
                ]);
            }

            $data['student_id'] = $fee->student_id;
        }
    }

    private function assertModule(string $module): void
    {
        abort_unless(isset(self::MODULES[$module]), 404);
    }

    private function options(string $type): array
    {
        return match ($type) {
            'courses' => Course::query()->orderBy('name')->pluck('name', 'id')->all(),
            'students' => Student::query()->orderBy('name')->get()->mapWithKeys(
                fn (Student $student) => [$student->id => $student->admission_number.' — '.$student->name]
            )->all(),
            'fees' => Fee::query()->with('student')->orderByDesc('due_date')->get()->mapWithKeys(
                fn (Fee $fee) => [$fee->id => $fee->student->admission_number.' — '.$fee->title.' (KSh '.number_format((float) $fee->amount, 2).')']
            )->all(),
            default => [],
        };
    }

    private function reports(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $payments = Payment::query()->with(['student', 'fee'])->when(
            $filters['from'] ?? null,
            fn (Builder $query, string $from) => $query->whereDate('paid_at', '>=', $from)
        )->when(
            $filters['to'] ?? null,
            fn (Builder $query, string $to) => $query->whereDate('paid_at', '<=', $to)
        );

        $attendance = Attendance::query()->when(
            $filters['from'] ?? null,
            fn (Builder $query, string $from) => $query->whereDate('attendance_date', '>=', $from)
        )->when(
            $filters['to'] ?? null,
            fn (Builder $query, string $to) => $query->whereDate('attendance_date', '<=', $to)
        );

        return view('management.reports', [
            'filters' => $filters,
            'totalStudents' => Student::query()->count(),
            'totalCourses' => Course::query()->count(),
            'payments' => (clone $payments)->latest('paid_at')->paginate(15)->withQueryString(),
            'feesCollected' => (clone $payments)->sum('amount'),
            'attendanceCounts' => (clone $attendance)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
