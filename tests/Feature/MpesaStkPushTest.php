<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\MpesaPaymentRequest;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MpesaStkPushTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.mpesa.environment' => 'sandbox',
            'services.mpesa.consumer_key' => 'sandbox-consumer-key',
            'services.mpesa.consumer_secret' => 'sandbox-consumer-secret',
            'services.mpesa.shortcode' => '174379',
            'services.mpesa.passkey' => 'sandbox-passkey',
            'services.mpesa.callback_url' => 'https://school-test.ngrok-free.dev',
            'services.mpesa.callback_secret' => 'test-callback-secret',
        ]);
    }

    public function test_m_pesa_stk_push_creates_a_pending_request_without_counting_it_as_paid(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['access_token' => 'sandbox-token']),
            'sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
                'ResponseCode' => '0',
                'CheckoutRequestID' => 'ws_CO_123',
                'MerchantRequestID' => 'merchant_123',
                'CustomerMessage' => 'STK Push sent',
            ]),
        ]);
        [$user, $fee] = $this->makeFee();

        $this->actingAs($user)->post(route('payments.store'), [
            'fee_id' => $fee->id,
            'amount' => '100',
            'paid_at' => '2026-10-15',
            'method' => 'mpesa',
            'payment_action' => 'stk_push',
            'phone' => '0712345678',
        ])->assertRedirect(route('payments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mpesa_payment_requests', [
            'fee_id' => $fee->id,
            'amount' => '100.00',
            'phone' => '254712345678',
            'checkout_request_id' => 'ws_CO_123',
            'merchant_request_id' => 'merchant_123',
            'status' => 'pending',
        ]);
        $this->assertSame(0, Payment::query()->count());
        $this->get(route('payments.create'))
            ->assertOk()
            ->assertSee('Balance: KSh 400.00');

        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/mpesa/stkpush/v1/processrequest')
            && $request['PhoneNumber'] === '254712345678'
            && $request['Amount'] === 100
            && $request['CallBackURL'] === 'https://school-test.ngrok-free.dev/payments/mpesa/callback/test-callback-secret'
            && $request['TransactionType'] === 'CustomerPayBillOnline');
    }

    public function test_successful_callback_records_payment_and_duplicate_callbacks_are_idempotent(): void
    {
        [$user, $fee] = $this->makeFee();
        $pending = MpesaPaymentRequest::query()->create([
            'fee_id' => $fee->id,
            'amount' => 100,
            'phone' => '254712345678',
            'account_reference' => 'STKTEST12345',
            'merchant_request_id' => 'merchant_456',
            'checkout_request_id' => 'ws_CO_456',
            'status' => 'pending',
        ]);
        $callback = [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'merchant_456',
                    'CheckoutRequestID' => 'ws_CO_456',
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is successful.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 100],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'TST123ABC'],
                            ['Name' => 'PhoneNumber', 'Value' => 254712345678],
                        ],
                    ],
                ],
            ],
        ];

        $url = route('payments.mpesa.callback', ['secret' => 'test-callback-secret']);
        $this->postJson($url, $callback)
            ->assertOk()
            ->assertJsonPath('ResultCode', 0);
        $this->postJson($url, $callback)
            ->assertOk()
            ->assertJsonPath('ResultDesc', 'Callback already processed.');

        $this->assertDatabaseHas('payments', [
            'fee_id' => $fee->id,
            'student_id' => $fee->student_id,
            'amount' => '100.00',
            'method' => 'mpesa',
            'reference' => 'TST123ABC',
            'mpesa_receipt_number' => 'TST123ABC',
            'mpesa_phone' => '254712345678',
            'status' => 'completed',
        ]);
        $this->assertSame(1, Payment::query()->count());
        $this->assertSame('completed', $pending->fresh()->status);
        $this->assertSame(400.0, $fee->fresh()->balance);

        $this->actingAs($user)->get(route('reports.index'))
            ->assertOk()
            ->assertSee('KSh 100.00')
            ->assertSee('1 payments')
            ->assertSee('TST123ABC');
    }

    public function test_callback_with_mismatched_request_ids_does_not_change_pending_payment(): void
    {
        [, $fee] = $this->makeFee();
        $pending = $this->makePendingRequest($fee, 'ws_CO_MISMATCH', 'merchant_expected');

        $this->postJson($this->callbackUrl(), $this->callbackPayload(
            'ws_CO_MISMATCH',
            'merchant_wrong',
            1,
            [],
        ))->assertOk()
            ->assertJsonPath('ResultDesc', 'Callback acknowledged; request identifiers did not match.');

        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_callback_with_wrong_amount_or_phone_does_not_complete_or_fail_pending_payment(): void
    {
        [, $fee] = $this->makeFee();
        $pending = $this->makePendingRequest($fee, 'ws_CO_INVALID_META', 'merchant_invalid_meta');

        $this->postJson($this->callbackUrl(), $this->callbackPayload(
            'ws_CO_INVALID_META',
            'merchant_invalid_meta',
            0,
            [
                ['Name' => 'Amount', 'Value' => 99],
                ['Name' => 'MpesaReceiptNumber', 'Value' => 'TSTINVALID'],
                ['Name' => 'PhoneNumber', 'Value' => 254712345678],
            ],
        ))->assertOk()
            ->assertJsonPath('ResultCode', 0);

        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame(0, Payment::query()->count());
        $this->assertSame(500.0, $fee->fresh()->balance);
    }

    public function test_duplicate_mpesa_receipt_cannot_complete_a_second_request(): void
    {
        [, $fee] = $this->makeFee();
        $firstRequest = $this->makePendingRequest($fee, 'ws_CO_FIRST_RECEIPT', 'merchant_first_receipt');
        $secondRequest = $this->makePendingRequest($fee, 'ws_CO_SECOND_RECEIPT', 'merchant_second_receipt');

        $metadata = [
            ['Name' => 'Amount', 'Value' => 100],
            ['Name' => 'MpesaReceiptNumber', 'Value' => 'TSTREUSED01'],
            ['Name' => 'PhoneNumber', 'Value' => 254712345678],
        ];

        $this->postJson($this->callbackUrl(), $this->callbackPayload(
            'ws_CO_FIRST_RECEIPT',
            'merchant_first_receipt',
            0,
            $metadata,
        ))->assertOk();

        $this->postJson($this->callbackUrl(), $this->callbackPayload(
            'ws_CO_SECOND_RECEIPT',
            'merchant_second_receipt',
            0,
            $metadata,
        ))->assertOk()
            ->assertJsonPath('ResultDesc', 'Duplicate receipt rejected.');

        $this->assertSame('completed', $firstRequest->fresh()->status);
        $this->assertSame('failed', $secondRequest->fresh()->status);
        $this->assertSame(1, Payment::query()->count());
        $this->assertSame(400.0, $fee->fresh()->balance);
    }

    public function test_verified_unsuccessful_callback_fails_request_without_creating_payment(): void
    {
        [, $fee] = $this->makeFee();
        $pending = $this->makePendingRequest($fee, 'ws_CO_CANCELLED', 'merchant_cancelled');

        $this->postJson($this->callbackUrl(), $this->callbackPayload(
            'ws_CO_CANCELLED',
            'merchant_cancelled',
            1032,
            [],
            'Request cancelled by user.',
        ))->assertOk()
            ->assertJsonPath('ResultDesc', 'Failed payment result recorded.');

        $this->assertSame('failed', $pending->fresh()->status);
        $this->assertSame(0, Payment::query()->count());
        $this->assertSame(500.0, $fee->fresh()->balance);
    }

    public function test_m_pesa_push_rejects_fractional_amounts_and_invalid_phone_numbers(): void
    {
        [$user, $fee] = $this->makeFee();
        $this->actingAs($user)->from(route('payments.create'))->post(route('payments.store'), [
            'fee_id' => $fee->id,
            'amount' => '10.50',
            'paid_at' => '2026-10-15',
            'method' => 'mpesa',
            'payment_action' => 'stk_push',
            'phone' => '0712345678',
        ])->assertSessionHasErrors('amount');

        $this->from(route('payments.create'))->post(route('payments.store'), [
            'fee_id' => $fee->id,
            'amount' => '10',
            'paid_at' => '2026-10-15',
            'method' => 'mpesa',
            'payment_action' => 'stk_push',
            'phone' => '123',
        ])->assertSessionHasErrors('phone');

        $this->assertSame(0, MpesaPaymentRequest::query()->count());
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_m_pesa_oauth_failure_shows_environment_specific_credentials_guidance(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
                'errorCode' => 'invalid_client',
                'errorMessage' => 'Invalid client credentials',
            ], 401),
        ]);
        [$user, $fee] = $this->makeFee();

        $this->actingAs($user)
            ->from(route('payments.create'))
            ->post(route('payments.store'), [
                'fee_id' => $fee->id,
                'amount' => '100',
                'paid_at' => '2026-10-15',
                'method' => 'mpesa',
                'payment_action' => 'stk_push',
                'phone' => '0712345678',
            ])
            ->assertRedirect(route('payments.create'))
            ->assertSessionHasErrors([
                'payment' => 'Safaricom rejected the consumer credentials (HTTP 401). Verify the key and secret are from the configured sandbox Daraja app and that the app has M-Pesa Express enabled.',
            ]);

        $this->assertSame(0, MpesaPaymentRequest::query()->count());
        $this->assertSame(0, Payment::query()->count());
    }

    private function makeFee(): array
    {
        $user = User::factory()->create();
        $student = Student::query()->create([
            'student_number' => 'MPESA-001',
            'first_name' => 'M-Pesa',
            'last_name' => 'Student',
            'status' => 'Active',
        ]);
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Tuition',
            'amount' => 500,
        ]);

        return [$user, $fee];
    }

    private function makePendingRequest(Fee $fee, string $checkoutRequestId, string $merchantRequestId): MpesaPaymentRequest
    {
        return MpesaPaymentRequest::query()->create([
            'fee_id' => $fee->id,
            'amount' => 100,
            'phone' => '254712345678',
            'account_reference' => strtoupper(str_replace('_', '', $checkoutRequestId)),
            'merchant_request_id' => $merchantRequestId,
            'checkout_request_id' => $checkoutRequestId,
            'status' => 'pending',
        ]);
    }

    private function callbackUrl(): string
    {
        return route('payments.mpesa.callback', ['secret' => 'test-callback-secret']);
    }

    private function callbackPayload(
        string $checkoutRequestId,
        string $merchantRequestId,
        int $resultCode,
        array $items,
        string $resultDescription = 'The service request is successful.',
    ): array {
        $callback = [
            'MerchantRequestID' => $merchantRequestId,
            'CheckoutRequestID' => $checkoutRequestId,
            'ResultCode' => $resultCode,
            'ResultDesc' => $resultDescription,
        ];

        if ($items !== []) {
            $callback['CallbackMetadata'] = ['Item' => $items];
        }

        return ['Body' => ['stkCallback' => $callback]];
    }
}
