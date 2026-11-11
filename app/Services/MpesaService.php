<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MpesaService
{
    /**
     * Get an access token from Safaricom Daraja.
     */
    public function getAccessToken(): string
    {
        $baseUrl = $this->baseUrl();

        $response = Http::withBasicAuth(
            config('services.mpesa.consumer_key'),
            config('services.mpesa.consumer_secret')
        )->acceptJson()->get(
            $baseUrl . '/oauth/v1/generate?grant_type=client_credentials'
        );

        if ($response->failed()) {
            Log::error('M-Pesa access token request failed.', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new RuntimeException(
                'Unable to obtain M-Pesa access token. Check your Daraja credentials.'
            );
        }

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException(
                'Safaricom did not return a valid access token.'
            );
        }

        return $token;
    }

    /**
     * Send an STK Push request.
     */
    public function stkPush(
        string $phone,
        float $amount,
        string $accountReference,
        string $transactionDescription = 'Student Fee Payment'
    ): array {
        $shortcode = (string) config('services.mpesa.shortcode');
        $passkey = (string) config('services.mpesa.passkey');
        $callbackUrl = (string) config('services.mpesa.callback_url');

        if (
            $shortcode === '' ||
            $passkey === '' ||
            $callbackUrl === ''
        ) {
            throw new RuntimeException(
                'M-Pesa shortcode, passkey, or callback URL is missing from configuration.'
            );
        }

        // Normalize a Kenyan mobile number to 254XXXXXXXXX.
        $phone = preg_replace('/[\s+\-]/', '', $phone) ?? '';

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') ||
                  str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }

        if (! preg_match('/^254[17]\d{8}$/', $phone)) {
            throw new RuntimeException(
                'Enter a valid Kenyan mobile number, such as 0712345678.'
            );
        }

        if ($amount < 1) {
            throw new RuntimeException(
                'The M-Pesa amount must be at least KSh 1.'
            );
        }

        $timestamp = now()->format('YmdHis');

        $password = base64_encode(
            $shortcode . $passkey . $timestamp
        );

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => config(
                'services.mpesa.transaction_type',
                'CustomerPayBillOnline'
            ),
            'Amount' => (int) round($amount),
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => substr($accountReference, 0, 12),
            'TransactionDesc' => substr($transactionDescription, 0, 13),
        ];

        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->post(
                $this->baseUrl() . '/mpesa/stkpush/v1/processrequest',
                $payload
            );

        if ($response->failed()) {
            Log::error('M-Pesa STK Push request failed.', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new RuntimeException(
                'Safaricom rejected the STK Push request. Check the application logs.'
            );
        }

        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException(
                'Safaricom returned an invalid STK Push response.'
            );
        }

        Log::info('M-Pesa STK Push response received.', [
            'response_code' => $result['ResponseCode'] ?? null,
            'checkout_request_id' => $result['CheckoutRequestID'] ?? null,
        ]);

        return $result;
    }

    /**
     * Select the Safaricom Daraja environment.
     */
    private function baseUrl(): string
    {
        return config('services.mpesa.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }
}

