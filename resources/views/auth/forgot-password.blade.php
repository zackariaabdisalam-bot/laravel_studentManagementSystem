<x-guest-layout>
    <main class="login-shell">
        <section class="login-main">
            <div class="login-card">
                <a class="login-brand d-inline-flex align-items-center gap-3 mb-4 text-decoration-none" href="{{ route('login') }}">
                    <span class="login-logo" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
                    <span>Student Management System</span>
                </a>

                <div class="mb-4">
                    <div class="login-logo mb-4" aria-hidden="true">
                        <i class="bi bi-envelope-arrow-up"></i>
                    </div>
                    <p class="text-primary fw-semibold text-uppercase small mb-2">Account recovery</p>
                    <h2 class="mb-2">Forgot your password?</h2>
                    <p class="text-secondary mb-0">
                        Enter the email address associated with your account and we’ll send you a link to reset your password.
                    </p>
                </div>

                <x-auth-session-status class="alert alert-success py-2 mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label">{{ __('Email address') }}</label>
                        <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <button type="submit" class="btn btn-primary login-submit w-100">
                        {{ __('Send reset link') }} <i class="bi bi-send ms-2" aria-hidden="true"></i>
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
