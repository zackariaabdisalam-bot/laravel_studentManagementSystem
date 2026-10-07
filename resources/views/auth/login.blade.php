<x-guest-layout>
    <main class="login-shell">
        <section class="login-main">
            <div class="login-card">
                <a class="login-brand d-inline-flex align-items-center gap-3 mb-4 text-decoration-none" href="{{ route('login') }}">
                    <span class="login-logo" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
                    <span>Student Management System</span>
                </a>
                <div class="mb-4">
                    <p class="text-primary fw-semibold text-uppercase small mb-2">Welcome back</p>
                    <h2 class="mb-2">Log in to your account</h2>
                    <p class="text-secondary small mb-0">Enter your details below to continue.</p>
                </div>

                <x-auth-session-status class="alert alert-success py-2 mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email address') }}</label>
                        <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                            <label for="remember_me" class="form-check-label text-secondary small">{{ __('Remember me') }}</label>
                        </div>

                        @if (Route::has('password.request'))
                            <a class="login-link" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary login-submit w-100">
                        {{ __('Sign in') }} <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="text-center text-secondary small mt-4 mb-0">
                    <i class="bi bi-shield-check me-1" aria-hidden="true"></i>
                    Your information is protected and secure.
                </p>
            </div>
        </section>
    </main>
</x-guest-layout>
