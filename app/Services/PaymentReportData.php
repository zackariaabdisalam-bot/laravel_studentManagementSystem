<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Collection;

class PaymentReportData
{
    public const METHODS = [
        'mpesa' => 'M-Pesa',
        'cash' => 'Cash',
        'bank' => 'Bank',
        'cheque' => 'Cheque',
    ];

    public function all(): Collection
    {
        return Payment::query()
            ->with(['student', 'fee'])
            ->where('status', 'completed')
            ->latest('paid_at')
            ->latest('id')
            ->get()
            ->map(fn (Payment $payment): array => [
                'date' => ($payment->paid_at ?? $payment->created_at)->format('Y-m-d'),
                'student' => $payment->student
                    ? trim($payment->student->first_name.' '.$payment->student->last_name)
                    : 'Student unavailable',
                'fee_structure' => $payment->fee?->title ?? 'Fee unavailable',
                'amount' => (float) $payment->amount,
                'method' => $this->normalizeMethod($payment->method),
                'reference' => $payment->reference ?? '',
                'receipt' => $payment->receipt_number
                    ?? 'RCP-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                'status' => 'Completed',
            ]);
    }

    private function normalizeMethod(string $method): string
    {
        $normalized = match (strtolower(str_replace(['-', ' '], '_', $method))) {
            'mpesa', 'm_pesa', 'mobile_money' => 'mpesa',
            'cash' => 'cash',
            'bank' => 'bank',
            'cheque', 'check' => 'cheque',
            default => null,
        };

        if ($normalized === null) {
            throw new \UnexpectedValueException("Unsupported payment method: {$method}");
        }

        return $normalized;
    }
}
