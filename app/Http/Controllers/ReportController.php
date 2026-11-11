<?php

namespace App\Http\Controllers;

use App\Services\PaymentReportData;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __invoke(Request $request, PaymentReportData $reportData): View|StreamedResponse
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'method' => ['nullable', 'in:all,mpesa,cash,bank,cheque'],
            'export' => ['nullable', 'in:csv'],
        ]);

        $filters = [
            'from' => $filters['from'] ?? '',
            'to' => $filters['to'] ?? '',
            'method' => $filters['method'] ?? 'all',
        ];

        $payments = $reportData->all()
            ->when($filters['from'], fn (Collection $payments) => $payments->where('date', '>=', $filters['from']))
            ->when($filters['to'], fn (Collection $payments) => $payments->where('date', '<=', $filters['to']))
            ->when($filters['method'] !== 'all', fn (Collection $payments) => $payments->where('method', $filters['method']))
            ->values();

        if (($request->query('export')) === 'csv') {
            return $this->downloadCsv($payments);
        }

        $methodTotals = collect(array_fill_keys(array_keys(PaymentReportData::METHODS), 0.0));
        foreach ($payments as $payment) {
            $methodTotals[$payment['method']] += $payment['amount'];
        }

        return view('management.reports', [
            'payments' => $payments,
            'filters' => $filters,
            'methodTotals' => $methodTotals,
            'totalCollected' => $payments->sum('amount'),
            'generatedAt' => now()->format('d M Y H:i'),
            'methods' => PaymentReportData::METHODS,
        ]);
    }

    private function downloadCsv(Collection $payments): StreamedResponse
    {
        return response()->streamDownload(function () use ($payments): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Unable to open the CSV output stream.');
            }

            fputcsv($output, ['Student', 'Receipt', 'Fee Structure', 'Amount', 'Method', 'Reference', 'Date', 'Status']);
            foreach ($payments as $payment) {
                fputcsv($output, [
                    $payment['student'],
                    $payment['receipt'],
                    $payment['fee_structure'],
                    number_format($payment['amount'], 2, '.', ''),
                    PaymentReportData::METHODS[$payment['method']],
                    $payment['reference'],
                    $payment['date'],
                    $payment['status'],
                ]);
            }
            fclose($output);
        }, 'payment-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
