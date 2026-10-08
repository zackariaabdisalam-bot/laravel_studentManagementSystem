<aside id="app-sidebar" class="sidebar position-fixed text-white shadow-sm"
       style="background: linear-gradient(180deg, #1d4ed8 0%, #1e3a8a 100%);">
    <div class="px-3 py-4">
        <div class="px-3 mb-3">
            <div class="text-uppercase text-white-50 fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                Student Management
            </div>
        </div>

        <nav class="nav nav-pills flex-column gap-2" aria-label="Main navigation">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active bg-white text-primary fw-semibold' : 'text-white' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>

            <a href="{{ route('students.index') }}"
               class="nav-link {{ request()->routeIs('students.*') ? 'active bg-white text-primary fw-semibold' : 'text-white' }}">
                <i class="bi bi-people me-2"></i>
                Students
            </a>

            <a href="{{ route('courses.index') }}"
               class="nav-link {{ request()->routeIs('courses.*') ? 'active bg-white text-primary fw-semibold' : 'text-white' }}">
                <i class="bi bi-book me-2"></i>
                Courses
            </a>
            <span class="nav-link text-white disabled opacity-50"
                  aria-disabled="true"
                  title="Unavailable">
                <i class="bi bi-cash-stack me-2"></i>
                Fees
            </span>
            <span class="nav-link text-white disabled opacity-50"
                  aria-disabled="true"
                  title="Unavailable">
                <i class="bi bi-credit-card me-2"></i>
                Payments
            </span>
            <span class="nav-link text-white disabled opacity-50"
                  aria-disabled="true"
                  title="Unavailable">
                <i class="bi bi-bar-chart me-2"></i>
                Reports
            </span>

            <span class="nav-link text-white disabled opacity-50"
                  aria-disabled="true"
                  title="Unavailable">
                <i class="bi bi-person-circle me-2"></i>
                Profile
            </span>
        </nav>
    </div>
</aside>
