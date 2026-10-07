<x-guest-layout>
    <main class="login-shell">
        <section class="login-main">
            <div class="login-card">
                <a class="login-brand d-inline-flex align-items-center gap-3 mb-4 text-decoration-none" href="{{ route('login') }}">
                    <span class="login-logo" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
                    <span>Student Management System</span>
                </a>

                <div class="mb-4">
                    <p class="text-primary fw-semibold text-uppercase small mb-2">Account recovery</p>
                    <h2 class="mb-2">{{ __('Create a new password') }}</h2>
                    <p class="text-secondary mb-0">Choose a strong password to secure your account.</p>
                </div>

                <x-auth-session-status class="alert alert-success py-2 mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email address') }}</label>
                        <input id="email" class="form-control" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="name@example.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('New password') }}</label>
                        <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password" placeholder="Enter a new password">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm new password') }}</label>
                        <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter your new password">
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button type="submit" class="btn btn-primary login-submit w-100">
                        {{ __('Reset password') }} <i class="bi bi-check2 ms-2" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a class="login-link" href="{{ route('login') }}">
                        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>{{ __('Back to sign in') }}
                    </a>
                </div>
            </div>
        </section>
    </main>
</x-guest-layout>
