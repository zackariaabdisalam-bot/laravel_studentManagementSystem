@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Record Payment</h1>
                <p class="text-secondary mb-0">Apply a payment to an outstanding student fee.</p>
            </div>
            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to Payments
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h2 class="h5 fw-bold mb-0">
                    <i class="bi bi-credit-card me-2" aria-hidden="true"></i>Payment Information
                </h2>
            </div>
            <div class="card-body p-3 p-md-4">
                <form method="POST" action="{{ route('payments.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="fee_id" class="form-label fw-semibold">Select student fee <span class="text-danger">*</span></label>
                            <select
                                name="fee_id"
                                id="fee_id"
                                class="form-select @error('fee_id') is-invalid @enderror"
                                aria-describedby="feeHelp feeBalance"
                                required
                            >
                                <option value="">Select a student and fee</option>
                                @foreach ($fees as $fee)
                                    <option
                                        value="{{ $fee->id }}"
                                        data-balance="{{ number_format($fee->available_balance, 2, '.', '') }}"
                                        @selected(old('fee_id') == $fee->id)
                                    >
                                        {{ trim(($fee->student?->first_name ?? '').' '.($fee->student?->last_name ?? '')) }}
                                        ({{ $fee->student?->student_number }}) —
                                        {{ $fee->title }} — Balance: KSh {{ number_format($fee->available_balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="feeHelp">Choose the student and fee the payment should be applied to.</div>
                            <div class="form-text fw-semibold text-primary" id="feeBalance" aria-live="polite">
                                @if (old('fee_id'))
                                    @php($selectedFee = $fees->firstWhere('id', old('fee_id')))
                                    @if ($selectedFee)
                                        Remaining fee balance: KSh {{ number_format($selectedFee->available_balance, 2) }}
                                    @else
                                        Choose a student fee to view its remaining balance.
                                    @endif
                                @else
                                    Choose a student fee to view its remaining balance.
                                @endif
                            </div>
                            @if ($fees->isEmpty())
                                <div class="alert alert-warning mt-3 mb-0" role="status">
                                    No student fees with an outstanding balance are available to pay.
                                    <a href="{{ route('fees.create') }}" class="alert-link">Create a fee</a>
                                    or check the existing fee balances.
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label for="method" class="form-label">Payment method <span class="text-danger">*</span></label>
                            <select
                                name="method"
                                id="method"
                                class="form-select @error('method') is-invalid @enderror"
                                required
                            >
                                <option value="mpesa" @selected(old('method', 'mpesa') === 'mpesa')>M-Pesa STK Push</option>
                                <option value="cash" @selected(old('method', 'cash') === 'cash')>Cash</option>
                                <option value="bank" @selected(old('method') === 'bank')>Bank</option>
                                <option value="cheque" @selected(old('method') === 'cheque')>Cheque</option>
                            </select>
                            @error('method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 d-none" id="phoneWrapper">
                            <label for="phone" class="form-label">M-Pesa phone number <span class="text-danger">*</span></label>
                            <input
                                type="tel"
                                name="phone"
                                id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}"
                                placeholder="0712345678 or 254712345678"
                                pattern="(?:\+?254|0)?[17][0-9]{8}"
                                autocomplete="tel"
                            >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">The student will receive an STK Push. The payment remains pending until Safaricom confirms it.</div>
                        </div>

                        <div class="col-md-6 d-none" id="amountWrapper">
                            <label for="amount" class="form-label">Amount to request (KSh) <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                name="amount"
                                id="amount"
                                class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}"
                                min="0.01"
                                step="0.01"
                                inputmode="decimal"
                                aria-describedby="amountPrompt"
                                disabled
                                required
                            >
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="amountPrompt" aria-live="polite">
                                Enter the amount to request from this phone number.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="paid_at" class="form-label">Payment date <span class="text-danger">*</span></label>
                            <input
                                type="date"
                                name="paid_at"
                                id="paid_at"
                                class="form-control @error('paid_at') is-invalid @enderror"
                                value="{{ old('paid_at', now()->toDateString()) }}"
                                required
                            >
                            @error('paid_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="referenceWrapper">
                            <label for="reference" class="form-label" id="referenceLabel">Cash Receipt Number</label>
                            <input
                                type="text"
                                name="reference"
                                id="reference"
                                class="form-control @error('reference') is-invalid @enderror"
                                value="{{ old('reference') }}"
                                maxlength="255"
                                autocomplete="off"
                            >
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0" role="note">
                        <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                        Cash, bank, and cheque entries are recorded as received by staff. M-Pesa is only recorded after a verified Safaricom callback.
                    </div>

                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary" id="submitButton" @disabled($fees->isEmpty())>
                            <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>
                            <span id="submitLabel">Record Payment</span>
                        </button>
                        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                    <input type="hidden" name="payment_action" id="paymentAction" value="">
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const method = document.getElementById('method');
            const phoneWrapper = document.getElementById('phoneWrapper');
            const phone = document.getElementById('phone');
            const referenceWrapper = document.getElementById('referenceWrapper');
            const referenceLabel = document.getElementById('referenceLabel');
            const referenceInput = document.getElementById('reference');
            const paymentAction = document.getElementById('paymentAction');
            const submitLabel = document.getElementById('submitLabel');
            const fee = document.getElementById('fee_id');
            const feeBalance = document.getElementById('feeBalance');
            const amount = document.getElementById('amount');
            const amountWrapper = document.getElementById('amountWrapper');
            const amountPrompt = document.getElementById('amountPrompt');
            const form = amount.form;
            const labels = {
                cash: 'Cash Receipt Number',
                bank: 'Bank Reference / Transaction ID',
                cheque: 'Cheque Number',
            };

            const updateMethodFields = () => {
                const isMpesa = method.value === 'mpesa';
                phoneWrapper.classList.toggle('d-none', !isMpesa);
                phone.required = isMpesa;
                referenceWrapper.classList.toggle('d-none', isMpesa);
                referenceInput.disabled = isMpesa;
                referenceLabel.textContent = labels[method.value] || 'Payment Reference';
                paymentAction.value = isMpesa ? 'stk_push' : '';
                submitLabel.textContent = isMpesa ? 'Send STK Push' : 'Record Payment';
                amount.step = isMpesa ? '1' : '0.01';
                amount.min = isMpesa ? '1' : '0.01';
                amount.inputMode = isMpesa ? 'numeric' : 'decimal';
                validateAmount();
                updateAmountVisibility();
            };

            const validateAmount = () => {
                const isMpesa = method.value === 'mpesa';
                const hasFraction = amount.value !== '' && !Number.isInteger(Number(amount.value));

                amount.setCustomValidity(
                    isMpesa && hasFraction
                        ? 'M-Pesa accepts whole Kenyan shillings only. Enter an amount such as 100, not 100.50.'
                        : ''
                );
            };

            const updateAmountVisibility = () => {
                const selectedFee = fee.options[fee.selectedIndex];
                const hasFee = Boolean(selectedFee && selectedFee.dataset.balance);
                const isMpesa = method.value === 'mpesa';
                const normalizedPhone = phone.value.replace(/[\s+-]/g, '');
                const validPhone = /^(?:254|0)?[17]\d{8}$/.test(normalizedPhone);
                const showAmount = hasFee && (!isMpesa || validPhone);

                amountWrapper.classList.toggle('d-none', !showAmount);
                amount.disabled = !showAmount;

                if (isMpesa && hasFee && !validPhone) {
                    amountPrompt.textContent = 'Enter a valid phone number to continue to the amount.';
                } else if (isMpesa && validPhone) {
                    amountPrompt.textContent = 'Enter whole Kenyan shillings only (for example, 100—not 100.50). Safaricom will ask the customer to approve this amount.';
                } else {
                    amountPrompt.textContent = 'Enter an amount up to the remaining fee balance.';
                }
            };

            const updateFeeBalance = () => {
                const selected = fee.options[fee.selectedIndex];
                const hasFee = Boolean(selected && selected.dataset.balance);

                amount.max = hasFee ? selected.dataset.balance : '';
                amount.placeholder = hasFee ? 'Enter an amount up to the fee balance' : 'Select a student fee first';

                if (!hasFee) {
                    feeBalance.textContent = 'Choose a student fee to view its remaining balance.';
                    return;
                }

                const balance = Number(selected.dataset.balance);
                feeBalance.textContent = `Remaining fee balance: KSh ${balance.toLocaleString('en-KE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;

                if (Number(amount.value) > balance) {
                    amount.value = '';
                }

                updateAmountVisibility();
            };

            method.addEventListener('change', updateMethodFields);
            phone.addEventListener('input', updateAmountVisibility);
            fee.addEventListener('change', updateFeeBalance);
            amount.addEventListener('input', validateAmount);
            form.addEventListener('submit', validateAmount);
            updateMethodFields();
            updateFeeBalance();
        });
    </script>
@endpush
