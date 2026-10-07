@props(['title', 'value', 'icon'])

<div class="card border-0 shadow-sm h-100">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="min-w-0">
                <p class="text-muted small mb-2">{{ $title }}</p>
                <h3 class="fw-semibold fs-5 mb-0 text-nowrap">{{ $value }}</h3>
            </div>
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary flex-shrink-0"
                 style="width: 3rem; height: 3rem;">
                <i class="bi {{ $icon }} fs-5" aria-hidden="true"></i>
            </div>
        </div>
    </div>
</div>
