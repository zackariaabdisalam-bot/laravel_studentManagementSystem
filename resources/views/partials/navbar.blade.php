<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top top-navbar border-bottom shadow-sm" aria-label="Top navigation">
    <div class="container-fluid px-3 px-lg-4">
        <button class="btn btn-light border me-2 d-md-none sidebar-toggle"
                type="button"
                aria-label="Toggle navigation menu"
                aria-controls="app-sidebar"
                aria-expanded="false">
            <i class="bi bi-list fs-4" aria-hidden="true"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2 gap-sm-3 me-2" href="{{ route('dashboard') }}">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fw-bold"
                  style="width: 38px; height: 38px;">
                SM
            </span>
            <span class="fw-bold text-dark d-none d-sm-inline">Student Management System</span>
        </a>

        <div class="d-flex align-items-center ms-auto">
            @auth
                <div class="dropdown">
                    <button class="btn p-1 p-sm-2 border-0 bg-transparent dropdown-toggle d-flex align-items-center"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-label="Open account menu">
                        <span class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-bold me-sm-2"
                              style="width: 38px; height: 38px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="d-none d-sm-block text-start">
                            <span class="d-block fw-semibold text-dark">{{ Auth::user()->name }}</span>
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><h6 class="dropdown-header">Account</h6></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-circle me-2" aria-hidden="true"></i>Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </div>
</nav>
