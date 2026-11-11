<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FeeController extends Controller
{
    private const FEE_NAMES = ['Semester 1', 'Semester 2', 'Semester 3'];

    // Display a paginated list of fees with student details and payment totals.
    public function index(): View
    {
        $fees = Fee::query()
            ->with('student')
            ->withSum([
                'payments as payments_sum_amount' => fn ($query) => $query
                    ->where('status', 'completed'),
            ], 'amount')
            ->orderByDesc('id')
            ->paginate(10);

        return view('fees.index', compact('fees'));
    }

    // Show the form for creating a fee and load the available students.
    public function create(): View
    {
        $students = Student::query()->orderBy('student_number')->get();
        $feeNames = self::FEE_NAMES;

        return view('fees.create', compact('students', 'feeNames'));
    }

    // Validate and save a new fee, then return to the fee list.
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'title' => ['required', 'string', Rule::in(self::FEE_NAMES)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        Fee::query()->create($validatedData);

        return redirect()->route('fees.index')->with('success', 'Fee created successfully.');
    }

    // Show the fee edit form with payment totals and available students.
    public function edit(Fee $fee): View
    {
        $fee->loadSum([
            'payments as payments_sum_amount' => fn ($query) => $query
                ->where('status', 'completed'),
        ], 'amount');
        $students = Student::query()->orderBy('student_number')->get();
        $feeNames = self::FEE_NAMES;

        return view('fees.edit', compact('fee', 'students', 'feeNames'));
    }

    // Validate and update a fee without invalidating existing payments.
    public function update(Request $request, Fee $fee): RedirectResponse
    {
        $validatedData = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'title' => ['required', 'string', Rule::in(array_unique([...self::FEE_NAMES, $fee->title]))],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($fee, $validatedData): void {
            $lockedFee = Fee::query()->lockForUpdate()->findOrFail($fee->id);
            $paidAmount = (float) $lockedFee->payments()
                ->where('status', 'completed')
                ->sum('amount');

            if ($paidAmount > 0 && (int) $validatedData['student_id'] !== (int) $lockedFee->student_id) {
                throw ValidationException::withMessages([
                    'student_id' => 'The student cannot be changed after payments have been recorded.',
                ]);
            }

            if ((float) $validatedData['amount'] < $paidAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'The fee amount cannot be less than the payments already recorded.',
                ]);
            }

            $lockedFee->update($validatedData);
        });

        return redirect()->route('fees.index')->with('success', 'Fee updated successfully.');
    }

    // Delete a fee only if no payments have been recorded for it.
    public function destroy(Fee $fee): RedirectResponse
    {
        if ($fee->payments()->exists()) {
            return redirect()->route('fees.index')
                ->withErrors(['fee' => 'Fees with recorded payments cannot be deleted.']);
        }

        $fee->delete();

        return redirect()->route('fees.index')->with('success', 'Fee deleted successfully.');
    }
}
