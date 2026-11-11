@php($editing = $student !== null)

<section class="card border-0 shadow-sm rounded-4 student-form-card">
    <div class="card-body p-3 p-md-4 p-xl-5">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-1">Please review the highlighted fields.</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $editing ? route('students.update', $student) : route('students.store') }}">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <section class="mb-4" aria-labelledby="student-details-title">
                <div class="form-section-heading mb-3">
                    <span class="form-section-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="student-details-title" class="h6 fw-bold mb-1">Student Details</h2>
                        <p class="small text-secondary mb-0">Enter the student’s name and identification details.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="student_number" class="form-label fw-semibold">Student Number <span class="text-danger">*</span></label>
                        <input id="student_number" type="text" name="student_number" value="{{ old('student_number', $student?->student_number) }}" class="form-control @error('student_number') is-invalid @enderror" maxlength="50" autocomplete="off" required>
                        @error('student_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="date_of_birth" class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $student?->date_of_birth?->format('Y-m-d')) }}" class="form-control @error('date_of_birth') is-invalid @enderror" required>
                        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $student?->first_name) }}" class="form-control @error('first_name') is-invalid @enderror" maxlength="255" autocomplete="given-name" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $student?->last_name) }}" class="form-control @error('last_name') is-invalid @enderror" maxlength="255" autocomplete="family-name" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                        @php($selectedGender = old('gender', $student?->gender))
                        <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="">Select gender</option>
                            @foreach (['Male', 'Female', 'Other'] as $gender)
                                <option value="{{ $gender }}" @selected($selectedGender === $gender)>{{ $gender }}</option>
                            @endforeach
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <section class="pt-4 border-top mb-4" aria-labelledby="student-contact-title">
                <div class="form-section-heading mb-3">
                    <span class="form-section-icon"><i class="bi bi-envelope-at" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="student-contact-title" class="h6 fw-bold mb-1">Contact Information</h2>
                        <p class="small text-secondary mb-0">Add a reliable email address and optional contact details.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email', $student?->email) }}" class="form-control @error('email') is-invalid @enderror" maxlength="255" autocomplete="email" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="phone" class="form-label fw-semibold">Phone Number</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone', $student?->phone) }}" class="form-control @error('phone') is-invalid @enderror" maxlength="13" placeholder="+2547XXXXXXXX" autocomplete="tel" aria-describedby="phone-help">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="phone-help" class="form-text">Kenyan mobile format: +2547 followed by 8 digits. Numbers entered as 07… are converted automatically.</div>
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label fw-semibold">Address</label>
                        <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror" autocomplete="street-address">{{ old('address', $student?->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <section class="pt-4 border-top" aria-labelledby="student-status-title">
                <div class="form-section-heading mb-3">
                    <span class="form-section-icon"><i class="bi bi-toggle-on" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="student-status-title" class="h6 fw-bold mb-1">Enrollment Status</h2>
                        <p class="small text-secondary mb-0">Choose whether the student is currently active.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        @php($selectedStatus = old('status', strtolower($student?->status ?? 'active')))
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" @selected($selectedStatus === 'active')>Active</option>
                            <option value="inactive" @selected($selectedStatus === 'inactive')>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-4 border-top">
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi {{ $editing ? 'bi-check-lg' : 'bi-person-plus' }} me-1" aria-hidden="true"></i>
                    {{ $editing ? 'Save Changes' : 'Add Student' }}
                </button>
            </div>
        </form>
    </div>
</section>

<style>
    .student-form-card .form-control,
    .student-form-card .form-select {
        min-height: 44px;
        border-color: #dbe2ea;
    }

    .student-form-card textarea.form-control {
        min-height: auto;
    }

    .form-section-heading {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .form-section-icon {
        display: inline-flex;
        width: 2.25rem;
        height: 2.25rem;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 0.65rem;
        color: #1d4ed8;
        background: #eff6ff;
    }
</style>
