<div>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-form">
        @csrf
        @method('patch')

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success d-flex align-items-center gap-2" role="status">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>Your profile information has been saved.</span>
            </div>
        @endif

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Full name') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="form-label">{{ __('Email address') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="small text-secondary mb-2">
                        {{ __('Your email address is unverified.') }}
                    </p>
                    <button form="send-verification" class="btn btn-sm btn-outline-primary">
                        {{ __('Resend verification email') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="small text-success mt-2 mb-0">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check2 me-1" aria-hidden="true"></i>{{ __('Save changes') }}
            </button>
            <span class="small text-secondary">Your email is used for account communication.</span>
        </div>
    </form>
</div>
