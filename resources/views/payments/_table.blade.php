<section aria-labelledby="transactions-title">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <h3 id="transactions-title" class="h6 fw-bold mb-0">{{ $tableTitle ?? 'Detailed Transactions' }}</h3>
        <span class="small text-secondary">
            {{ method_exists($payments, 'total') ? $payments->total() : $payments->count() }} payments
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0 report-table">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Receipt</th>
                    <th>Fee Structure</th>
                    <th class="text-end">Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    @php
                        $isReportRow = is_array($payment);
                        $student = $isReportRow
                            ? $payment['student']
                            : (trim(($payment->student?->first_name ?? '').' '.($payment->student?->last_name ?? '')) ?: 'Student unavailable');
                        $receipt = $isReportRow
                            ? $payment['receipt']
                            : ($payment->receipt_number ?: 'RCP-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT));
                        $feeTitle = $isReportRow ? $payment['fee_structure'] : ($payment->fee?->title ?? 'Fee unavailable');
                        $amount = $payment['amount'];
                        $method = $payment['method'];
                        $reference = $payment['reference'] ?: '—';
                        $date = $isReportRow ? \Illuminate\Support\Carbon::parse($payment['date']) : $payment->paid_at;
                        $status = $payment['status'];
                    @endphp
                    <tr>
                        <td>
                            {{ $student }}
                            @unless ($isReportRow)
                                <span class="d-block small text-secondary">{{ $payment->student?->student_number }}</span>
                            @endunless
                        </td>
                        <td class="text-nowrap">{{ $receipt }}</td>
                        <td>{{ $feeTitle }}</td>
                        <td class="text-end text-nowrap">KSh {{ number_format((float) $amount, 2) }}</td>
                        <td>{{ $methods[$method] ?? ucfirst($method) }}</td>
                        <td class="text-nowrap">{{ $reference }}</td>
                        <td class="text-nowrap">{{ $date?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <span class="badge {{ strtolower($status) === 'completed' ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">No payments match the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (method_exists($payments, 'links'))
        <div class="mt-3">{{ $payments->links() }}</div>
    @endif
</section>
