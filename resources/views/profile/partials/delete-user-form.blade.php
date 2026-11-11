<div>
    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
        <i class="bi bi-trash3 me-1" aria-hidden="true"></i>{{ __('Delete account') }}
    </button>

    <div class="modal fade" id="confirm-user-deletion" tabindex="-1" aria-labelledby="confirm-user-deletion-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

                    <div class="modal-header">
                        <h2 class="modal-title fs-5 fw-bold text-danger" id="confirm-user-deletion-title">
                            {{ __('Delete your account?') }}
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary">
                            {{ __('This permanently deletes your account and cannot be undone. Enter your password to confirm.') }}
                        </p>
                        <label for="delete-account-password" class="form-label">{{ __('Current password') }}</label>
                        <input id="delete-account-password" name="password" type="password"
                               class="form-control @if ($errors->userDeletion->has('password')) is-invalid @endif"
                               autocomplete="current-password" required>
                        @if ($errors->userDeletion->has('password'))
                            <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Delete account') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('confirm-user-deletion')).show();
            });
        </script>
    @endpush
@endif
