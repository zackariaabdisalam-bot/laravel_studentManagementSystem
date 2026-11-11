<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\MpesaPaymentRequest;
use App\Models\Payment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) $request->route('secret');
        $configuredSecret = (string) config('services.mpesa.callback_secret');

        if ($configuredSecret === '' || ! hash_equals($configuredSecret, $secret)) {
            Log::warning('M-Pesa callback rejected because its callback secret is invalid.');

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Unauthorized callback.',
            ], 403);
        }

        $callback = $request->input('Body.stkCallback');
        $checkoutRequestId = is_array($callback) ? ($callback['CheckoutRequestID'] ?? null) : null;
        $merchantRequestId = is_array($callback) ? ($callback['MerchantRequestID'] ?? null) : null;
        $resultCode = is_array($callback) ? ($callback['ResultCode'] ?? null) : null;

        if (
            ! is_array($callback)
            || ! is_string($checkoutRequestId)
            || ! filled($checkoutRequestId)
            || ! is_string($merchantRequestId)
            || ! filled($merchantRequestId)
            || (! is_int($resultCode) && (! is_string($resultCode) || ! preg_match('/^-?\d+$/', $resultCode)))
        ) {
            Log::warning('Invalid M-Pesa callback identifiers or result code received.');

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Invalid callback payload.',
            ], 400);
        }

        $pending = MpesaPaymentRequest::query()
            ->where('checkout_request_id', $checkoutRequestId)
            ->first();

        if (! $pending) {
            Log::warning('M-Pesa callback refers to an unknown checkout request.');

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Callback acknowledged; no matching request.',
            ]);
        }

        if (! filled($pending->merchant_request_id)
            || ! hash_equals((string) $pending->merchant_request_id, $merchantRequestId)) {
            Log::warning('M-Pesa callback merchant request ID does not match the pending request.', [
                'checkout_request_id' => $checkoutRequestId,
            ]);

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Callback acknowledged; request identifiers did not match.',
            ]);
        }

        $resultDescription = is_string($callback['ResultDesc'] ?? null)
            ? $callback['ResultDesc']
            : 'No result description supplied.';

        if ((int) $resultCode !== 0) {
            $updated = MpesaPaymentRequest::query()
                ->whereKey($pending->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'failed',
                    'result_description' => $resultDescription,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => $updated > 0 ? 'Failed payment result recorded.' : 'Callback already processed.',
            ]);
        }

        $metadataItems = data_get($callback, 'CallbackMetadata.Item');
        if (! is_array($metadataItems)) {
            return $this->rejectCallback($checkoutRequestId, 'Successful callback metadata is missing.');
        }

        $metadata = collect($metadataItems)
            ->filter(fn ($item) => is_array($item) && is_string($item['Name'] ?? null))
            ->mapWithKeys(fn (array $item) => [$item['Name'] => $item['Value'] ?? null]);

        $receipt = $metadata->get('MpesaReceiptNumber');
        $amount = $metadata->get('Amount');
        $phone = $metadata->get('PhoneNumber');

        if (
            ! is_string($receipt)
            || ! filled($receipt)
            || ! is_numeric($amount)
            || (float) $amount < 1
            || floor((float) $amount) !== (float) $amount
            || ! is_numeric($phone)
        ) {
            return $this->rejectCallback($checkoutRequestId, 'Successful callback receipt, amount, or phone is invalid.');
        }

        $receivedPhone = preg_replace('/\D+/', '', (string) $phone) ?? '';
        $expectedPhone = preg_replace('/\D+/', '', (string) $pending->phone) ?? '';

        if (
            (int) round((float) $amount * 100) !== (int) round((float) $pending->amount * 100)
            || $receivedPhone !== $expectedPhone
        ) {
            return $this->rejectCallback($checkoutRequestId, 'Callback amount or phone does not match the STK request.');
        }

        try {
            $result = DB::transaction(function () use (
                $pending,
                $checkoutRequestId,
                $merchantRequestId,
                $receipt,
                $resultDescription
            ): string {
                $lockedRequest = MpesaPaymentRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($pending->id);

                if ($lockedRequest->status !== 'pending') {
                    return 'already_processed';
                }

                if (
                    ! hash_equals((string) $lockedRequest->checkout_request_id, $checkoutRequestId)
                    || ! hash_equals((string) $lockedRequest->merchant_request_id, $merchantRequestId)
                ) {
                    return 'request_mismatch';
                }

                $fee = Fee::query()
                    ->lockForUpdate()
                    ->find($lockedRequest->fee_id);

                if (! $fee) {
                    $lockedRequest->update([
                        'status' => 'failed',
                        'result_description' => 'Associated fee record was not found.',
                    ]);

                    return 'fee_missing';
                }

                $existingPayment = Payment::query()
                    ->where('reference', $receipt)
                    ->orWhere('mpesa_receipt_number', $receipt)
                    ->first();

                if ($existingPayment) {
                    if (
                        (int) $lockedRequest->payment_id !== (int) $existingPayment->id
                        || (int) $existingPayment->fee_id !== (int) $lockedRequest->fee_id
                        || (int) round((float) $existingPayment->amount * 100) !== (int) round((float) $lockedRequest->amount * 100)
                        || $existingPayment->method !== 'mpesa'
                    ) {
                        $lockedRequest->update([
                            'status' => 'failed',
                            'result_description' => 'M-Pesa receipt has already been used by another payment.',
                        ]);

                        return 'duplicate_receipt';
                    }

                    $payment = $existingPayment;
                } else {
                    $payment = Payment::query()->create([
                        'fee_id' => $lockedRequest->fee_id,
                        'student_id' => $fee->student_id,
                        'amount' => $lockedRequest->amount,
                        'paid_at' => today(),
                        'method' => 'mpesa',
                        'reference' => $receipt,
                        'mpesa_receipt_number' => $receipt,
                        'mpesa_phone' => $lockedRequest->phone,
                        'status' => 'completed',
                    ]);
                }

                $lockedRequest->update([
                    'payment_id' => $payment->id,
                    'status' => 'completed',
                    'result_description' => $resultDescription,
                ]);

                return 'completed';
            });
        } catch (UniqueConstraintViolationException $exception) {
            Log::warning('M-Pesa callback receipt violated a payment uniqueness constraint.', [
                'checkout_request_id' => $checkoutRequestId,
            ]);

            MpesaPaymentRequest::query()
                ->whereKey($pending->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'failed',
                    'result_description' => 'M-Pesa receipt has already been used by another payment.',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Duplicate receipt rejected.',
            ]);
        }

        if ($result === 'request_mismatch' || $result === 'duplicate_receipt' || $result === 'fee_missing') {
            Log::warning('M-Pesa callback was not applied to the pending payment.', [
                'checkout_request_id' => $checkoutRequestId,
                'reason' => $result,
            ]);
        }

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => match ($result) {
                'completed' => 'Callback processed; payment completed.',
                'already_processed' => 'Callback already processed.',
                'request_mismatch' => 'Callback acknowledged; request identifiers did not match.',
                'duplicate_receipt' => 'Duplicate receipt rejected.',
                'fee_missing' => 'Payment could not be applied because the fee was not found.',
                default => 'Callback acknowledged.',
            },
        ]);
    }

    private function rejectCallback(string $checkoutRequestId, string $reason): JsonResponse
    {
        Log::warning('M-Pesa callback verification failed.', [
            'checkout_request_id' => $checkoutRequestId,
            'reason' => $reason,
        ]);

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Callback acknowledged; payment remains pending because verification failed.',
        ]);
    }
}
