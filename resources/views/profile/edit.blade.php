@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
    <style>
        .profile-hero {
            background: linear-gradient(120deg, #1e3a8a 0%, #2563eb 62%, #3b82f6 100%);
        }

        .profile-avatar {
            width: 76px;
            height: 76px;
            flex: 0 0 76px;
            font-size: 1.65rem;
            letter-spacing: 0.03em;
        }

        .profile-section-card {
            border: 0;
            box-shadow: 0 4px 18px rgb(15 23 42 / 5%);
        }

        .profile-form .form-label {
            color: #334155;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .profile-form .form-control {
            min-height: 46px;
            border-color: #dbe2ea;
        }

        .profile-form .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.2rem rgb(13 110 253 / 12%);
        }
    </style>
@endpush

@section('content')
    @php
        $nameParts = collect(preg_split('/\s+/', trim($user->name)))->filter();
        $initials = $nameParts->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    @endphp

    <div class="container-fluid px-0">
        <div class="mb-4">
            <p class="text-uppercase text-primary fw-semibold small mb-1">Account settings</p>
            <h1 class="h2 fw-bold mb-1">My profile</h1>
            <p class="text-secondary mb-0">Manage your personal information and keep your account secure.</p>
        </div>

        <section class="profile-hero card border-0 shadow-sm text-white mb-4" aria-label="Profile overview">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 gap-lg-4">
                    <div class="profile-avatar rounded-circle bg-white bg-opacity-25 border border-white border-opacity-25 d-flex align-items-center justify-content-center fw-bold"
                         aria-hidden="true">
                        {{ mb_strtoupper($initials ?: 'U') }}
                    </div>
                    <div class="flex-grow-1">
                        <span class="badge rounded-pill bg-white bg-opacity-25 text-white mb-2">ACCOUNT PROFILE</span>
                        <h2 class="h3 fw-bold mb-1">{{ $user->name }}</h2>
                        <p class="mb-0 opacity-75">{{ $user->email }}</p>
                    </div>
                    <div class="text-sm-end">
                        <div class="small opacity-75 mb-1">Member since</div>
                        <div class="fw-semibold">{{ $user->created_at?->format('F Y') ?? 'Account active' }}</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-3 g-xl-4">
            <div class="col-12 col-xl-7">
                <section class="profile-section-card card h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary"
                                  style="width: 42px; height: 42px;" aria-hidden="true">
                                <i class="bi bi-person-lines-fill fs-5"></i>
                            </span>
                            <div>
                                <h2 class="h5 fw-bold mb-1">Personal information</h2>
                                <p class="small text-secondary mb-0">Update the name and email linked to your account.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-5">
                <section class="profile-section-card card h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success"
                                  style="width: 42px; height: 42px;" aria-hidden="true">
                                <i class="bi bi-shield-lock fs-5"></i>
                            </span>
                            <div>
                                <h2 class="h5 fw-bold mb-1">Password and security</h2>
                                <p class="small text-secondary mb-0">Use a strong, unique password.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @include('profile.partials.update-password-form')
                    </div>
                </section>
            </div>

            <div class="col-12">
                <section class="profile-section-card card border-start border-4 border-danger">
                    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h2 class="h5 fw-bold text-danger mb-1">Delete account</h2>
                            <p class="small text-secondary mb-0">Permanently remove your account. This action cannot be undone.</p>
                        </div>
                        @include('profile.partials.delete-user-form')
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
