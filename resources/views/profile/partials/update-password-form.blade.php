<div>
    <form method="post" action="{{ route('password.update') }}" class="profile-form">
        @csrf
        @method('put')

        @if (session('status') === 'password-updated')
            <div class="alert alert-success d-flex align-items-center gap-2" role="status">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>Your password has been updated.</span>
            </div>
        @endif

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">{{ __('Current password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password"
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">{{ __('New password') }}</label>
            <input id="update_password_password" name="password" type="password"
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm new password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="bi bi-key me-1" aria-hidden="true"></i>{{ __('Update password') }}
            </button>
        </div>
    </form>
</div>
