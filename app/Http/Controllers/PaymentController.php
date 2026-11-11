<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\MpesaPaymentRequest;
use App\Models\Payment;
use App\Services\MpesaStkPush;
use App\Services\PaymentReportData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentController extends Controller
{
    /**
     * Display all payments.
     */
    public function index(Request $request)
    {
        $query = Payment::query()->with(['student', 'receivedBy', 'fee']);

        // Search by student details.
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where('student_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Apply status filtering only if the column exists.
        if (
            $request->filled('status')
            && Schema::hasColumn('payments', 'status')
        ) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('method') && array_key_exists($request->input('method'), PaymentReportData::METHODS)) {
            $query->where('method', $request->input('method'));
        }

        $payments = $query->latest()->paginate(10)->withQueryString();

        // Calculate total collected.
        $totalCollected = Payment::query()
            ->when(
                Schema::hasColumn('payments', 'status'),
                fn ($q) => $q->where('status', 'completed')
            )
            ->sum('amount');

        // Calculate payments collected this month.
        $thisMonth = 0;

        if (Schema::hasColumn('payments', 'paid_at')) {
            $thisMonth = Payment::query()
                ->when(
                    Schema::hasColumn('payments', 'status'),
                    fn ($q) => $q->where('status', 'completed')
                )
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount');
        }

        // Pending M-Pesa total, if the status column exists.
        $pendingMpesa = 0;

        if (Schema::hasColumn('payments', 'status')) {
            $pendingMpesa = Payment::query()
                ->where('status', 'pending')
                ->sum('amount');
        }

        $mpesaRequests = MpesaPaymentRequest::query()
            ->with(['fee.student'])
            ->whereIn('status', ['pending', 'failed'])
            ->latest()
            ->limit(20)
            ->get();
        $methods = PaymentReportData::METHODS;

        return view('payments.index', compact(
            'payments',
            'totalCollected',
            'thisMonth',
            'pendingMpesa',
            'mpesaRequests',
            'methods'
        ));
    }

    /**
     * Show the payment creation form.
     */
    public function create()
    {
        $fees = Fee::query()
            ->with('student')
            ->withSum([
                'payments as completed_payments_sum_amount' => fn ($query) => $query
                    ->where('status', 'completed'),
            ], 'amount')
            ->orderByDesc('id')
            ->get();

        $pendingAmounts = MpesaPaymentRequest::query()
            ->where('status', 'pending')
            ->selectRaw('fee_id, SUM(amount) as pending_amount')
            ->groupBy('fee_id')
            ->pluck('pending_amount', 'fee_id');

        $fees = $fees->map(function (Fee $fee) use ($pendingAmounts): Fee {
            $fee->available_balance = max(
                0,
                (float) $fee->amount
                    - (float) ($fee->completed_payments_sum_amount ?? 0)
                    - (float) ($pendingAmounts[$fee->id] ?? 0)
            );

            return $fee;
        })->filter(fn (Fee $fee): bool => $fee->available_balance > 0)->values();

        return view('payments.create', compact('fees'));
    }

    /**
     * Store a staff-confirmed payment or initiate an M-Pesa STK Push.
     */
    public function store(Request $request, MpesaStkPush $mpesa): RedirectResponse
    {
        $validated = $request->validate([
            'fee_id' => ['required', 'integer', 'exists:fees,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'paid_at' => ['required', 'date'],
            'method' => ['required', 'in:mpesa,cash,bank,cheque'],
            'payment_action' => ['nullable', 'in:stk_push'],
            'phone' => ['required_if:method,mpesa', 'nullable', 'regex:/^(?:\+?254|0)?[17]\d{8}$/'],
            'reference' => ['nullable', 'string', 'max:255', 'unique:payments,reference'],
        ]);

        if ($validated['method'] === 'mpesa') {
            if (($validated['payment_action'] ?? null) !== 'stk_push') {
                throw ValidationException::withMessages([
                    'method' => 'M-Pesa payments must be confirmed by Safaricom. Start an STK Push instead of recording a receipt manually.',
                ]);
            }

            if (floor((float) $validated['amount']) !== (float) $validated['amount']) {
                throw ValidationException::withMessages([
                    'amount' => 'M-Pesa payments must be a whole amount in Kenyan shillings.',
                ]);
            }

            try {
                $fee = Fee::query()->findOrFail($validated['fee_id']);
                $mpesa->initiate($fee, (float) $validated['amount'], $validated['phone']);
            } catch (RuntimeException $exception) {
                return back()->withInput()->withErrors([
                    'payment' => $exception->getMessage(),
                ]);
            }

            return redirect()->route('payments.index')
                ->with('success', 'M-Pesa STK Push sent. The payment will be recorded after Safaricom confirms it.');
        }

        DB::transaction(function () use ($validated, $request): void {
            $fee = Fee::query()->lockForUpdate()->findOrFail($validated['fee_id']);
            $paidAmount = (float) $fee->payments()
                ->where('status', 'completed')
                ->sum('amount');
            $pendingAmount = (float) MpesaPaymentRequest::query()
                ->where('fee_id', $fee->id)
                ->where('status', 'pending')
                ->sum('amount');
            $balance = max(0, (float) $fee->amount - $paidAmount - $pendingAmount);
            $amount = (float) $validated['amount'];

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment exceeds the remaining fee balance.',
                ]);
            }

            Payment::query()->create([
                'fee_id' => $fee->id,
                'student_id' => $fee->student_id,
                'amount' => $amount,
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'paid_at' => $validated['paid_at'],
                'status' => 'completed',
                'received_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display payment details.
     */
    public function show(Payment $payment)
    {
        $payment->load(['student', 'receivedBy', 'feeStructure']);

        $totalFee = $payment->feeStructure?->amount ?? 0;

        $paidAmount = Payment::query()
            ->where('student_id', $payment->student_id)
            ->where('fee_id', $payment->fee_id)
            ->when(
                Schema::hasColumn('payments', 'status'),
                fn ($q) => $q->where('status', 'completed')
            )
            ->sum('amount');

        $balance = max((float) $totalFee - (float) $paidAmount, 0);

        return view('payments.show', compact(
            'payment',
            'totalFee',
            'paidAmount',
            'balance'
        ));
    }

    /**
     * Show the payment editing form.
     */
    public function edit(string $id)
    {
        $payment = Payment::findOrFail($id);

        return view('payments.edit', compact('payment'));
    }

    /**
     * Update a payment.
     */
    public function update(Request $request, string $id)
    {
        return redirect()
            ->route('payments.index')
            ->with('error', 'Payment updating has not been configured yet.');
    }

    /**
     * Delete a payment.
     */
    public function destroy(string $id)
    {
        return redirect()
            ->route('payments.index')
            ->with('error', 'Payment deletion has not been configured yet.');
    }
}
