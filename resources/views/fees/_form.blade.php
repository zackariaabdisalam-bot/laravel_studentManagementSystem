<div class="row g-3">
    <div class="col-md-6">
        <label for="student_id" class="form-label">Student</label>
        <select id="student_id" name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
            <option value="">Select a student</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}"
                    @selected((string) old('student_id', $fee->student_id ?? '') === (string) $student->id)>
                    {{ $student->student_number }} — {{ $student->first_name }} {{ $student->last_name }}
                </option>
            @endforeach
        </select>
        @error('student_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($students->isEmpty())
            <div class="form-text text-danger">Add a student before creating a fee.</div>
        @endif
    </div>
    <div class="col-md-6">
        <label for="title" class="form-label">Semester</label>
        <select id="title" name="title" class="form-select @error('title') is-invalid @enderror" required>
            <option value="">Select a semester</option>
            @if (isset($fee) && !in_array($fee->title, $feeNames, true))
                <option value="{{ $fee->title }}" @selected(old('title', $fee->title) === $fee->title)>
                    {{ $fee->title }} (existing fee name)
                </option>
            @endif
            @foreach ($feeNames as $feeName)
                <option value="{{ $feeName }}" @selected(old('title', $fee->title ?? '') === $feeName)>
                    {{ $feeName }}
                </option>
            @endforeach
        </select>
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="amount" class="form-label">Amount</label>
        <input id="amount" name="amount" type="number" min="0.01" step="0.01"
               value="{{ old('amount', $fee->amount ?? '') }}"
               class="form-control @error('amount') is-invalid @enderror" required>
        @error('amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="due_date" class="form-label">Due Date <span class="text-muted">(optional)</span></label>
        <input id="due_date" name="due_date" type="date"
               value="{{ old('due_date', isset($fee) ? $fee->due_date?->format('Y-m-d') : '') }}"
               class="form-control @error('due_date') is-invalid @enderror">
        @error('due_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12">
        <label for="notes" class="form-label">Notes <span class="text-muted">(optional)</span></label>
        <textarea id="notes" name="notes" rows="3"
                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $fee->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn school-primary" @disabled($students->isEmpty())>
        <i class="bi bi-check-circle me-1"></i>
        {{ isset($fee) ? 'Update Fee' : 'Save Fee' }}
    </button>
</div>
