<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\MpesaPaymentRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class MpesaStkPush
{
    public function isConfigured(): bool
    {
        return filled(config('services.mpesa.consumer_key'))
            && filled(config('services.mpesa.consumer_secret'))
            && filled(config('services.mpesa.shortcode'))
            && filled(config('services.mpesa.passkey'))
            && filled(config('services.mpesa.callback_secret'))
            && $this->hasValidCallbackUrl()
            && in_array(config('services.mpesa.environment'), ['sandbox', 'production'], true);
    }

    public function initiate(Fee $fee, float $amount, string $phone): MpesaPaymentRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('M-Pesa STK Push is not configured. Add the Daraja sandbox credentials and callback secret to the application environment.');
        }

        $phone = preg_replace('/[\s+\-]/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254'.$phone;
        }

        if (! preg_match('/^254[17]\d{8}$/', $phone)) {
            throw new RuntimeException('Enter a valid Kenyan mobile number, such as 0712345678.');
        }

        if ($amount < 1 || floor($amount) !== $amount) {
            throw new RuntimeException('M-Pesa payments must be a whole amount of at least KSh 1.');
        }

        $baseUrl = config('services.mpesa.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        try {
            $tokenResponse = Http::withBasicAuth(
                config('services.mpesa.consumer_key'),
                config('services.mpesa.consumer_secret'),
            )->acceptJson()->get($baseUrl.'/oauth/v1/generate', [
                'grant_type' => 'client_credentials',
            ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Could not connect to Safaricom to authorize the STK Push request.', previous: $exception);
        }

        if (! $tokenResponse->successful()) {
            $errorCode = $tokenResponse->json('errorCode');

            Log::warning('M-Pesa OAuth authorization failed.', [
                'http_status' => $tokenResponse->status(),
                'error_code' => is_scalar($errorCode) ? (string) $errorCode : null,
                'environment' => config('services.mpesa.environment'),
            ]);

            if (in_array($tokenResponse->status(), [401, 403], true)) {
                throw new RuntimeException(
                    'Safaricom rejected the consumer credentials (HTTP '.$tokenResponse->status().'). Verify the key and secret are from the configured '
                    .config('services.mpesa.environment')
                    .' Daraja app and that the app has M-Pesa Express enabled.'
                );
            }

            throw new RuntimeException(
                'Safaricom token request failed with HTTP '.$tokenResponse->status().'. Check the Daraja environment and try again.'
            );
        }

        if (! filled($tokenResponse->json('access_token'))) {
            Log::warning('M-Pesa OAuth response did not contain an access token.', [
                'http_status' => $tokenResponse->status(),
                'environment' => config('services.mpesa.environment'),
            ]);

            throw new RuntimeException('Safaricom authorized the request but did not return an access token.');
        }

        $pending = DB::transaction(function () use ($fee, $amount, $phone): MpesaPaymentRequest {
            $lockedFee = Fee::query()->lockForUpdate()->findOrFail($fee->id);
            $paidAmount = (float) $lockedFee->payments()
                ->where('status', 'completed')
                ->sum('amount');
            $pendingAmount = (float) MpesaPaymentRequest::query()
                ->where('fee_id', $lockedFee->id)
                ->where('status', 'pending')
                ->sum('amount');
            $balance = max(0, (float) $lockedFee->amount - $paidAmount - $pendingAmount);

            if ($amount > $balance) {
                throw new RuntimeException('The payment exceeds the remaining fee balance, including pending M-Pesa requests.');
            }

            return MpesaPaymentRequest::query()->create([
                'fee_id' => $lockedFee->id,
                'amount' => $amount,
                'phone' => $phone,
                'account_reference' => 'S'.Str::upper(Str::random(11)),
                'status' => 'pending',
            ]);
        });

        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $shortcode = (string) config('services.mpesa.shortcode');
        $password = base64_encode($shortcode.config('services.mpesa.passkey').$timestamp);
        $callbackUrl = $this->callbackUrl();

        try {
            $response = Http::withToken($tokenResponse->json('access_token'))
                ->acceptJson()
                ->post($baseUrl.'/mpesa/stkpush/v1/processrequest', [
                    'BusinessShortCode' => $shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => (int) $amount,
                    'PartyA' => $phone,
                    'PartyB' => $shortcode,
                    'PhoneNumber' => $phone,
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => $pending->account_reference,
                    'TransactionDesc' => 'Student fee payment',
                ]);
        } catch (ConnectionException $exception) {
            $pending->update([
                'status' => 'failed',
                'result_description' => 'Could not connect to Safaricom to initiate the payment.',
            ]);
            throw new RuntimeException('Could not connect to Safaricom. Please try again.', previous: $exception);
        }

        if (! $response->successful() || (string) $response->json('ResponseCode') !== '0'
            || ! filled($response->json('CheckoutRequestID'))
            || ! filled($response->json('MerchantRequestID'))) {
            $pending->update([
                'status' => 'failed',
                'result_description' => $response->json('errorMessage')
                    ?? $response->json('ResponseDescription')
                    ?? 'Safaricom rejected the STK Push request.',
            ]);
            throw new RuntimeException('Safaricom could not start the M-Pesa payment. Check the phone number and sandbox configuration.');
        }

        $pending->update([
            'merchant_request_id' => $response->json('MerchantRequestID'),
            'checkout_request_id' => $response->json('CheckoutRequestID'),
            'result_description' => $response->json('CustomerMessage'),
        ]);

        return $pending;
    }

    private function hasValidCallbackUrl(): bool
    {
        $callbackUrl = config('services.mpesa.callback_url');
        if (! is_string($callbackUrl) || ! filled($callbackUrl)) {
            return false;
        }

        $parts = parse_url($callbackUrl);

        return is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && filled($parts['host'] ?? null);
    }

    private function callbackUrl(): string
    {
        if (! $this->hasValidCallbackUrl()) {
            throw new RuntimeException('Set MPESA_CALLBACK_URL to the public HTTPS base URL of your callback tunnel.');
        }

        $parts = parse_url((string) config('services.mpesa.callback_url'));
        $origin = 'https://'.$parts['host']
            .(isset($parts['port']) ? ':'.$parts['port'] : '');

        return $origin.route('payments.mpesa.callback', [
            'secret' => config('services.mpesa.callback_secret'),
        ], false);
    }
}
