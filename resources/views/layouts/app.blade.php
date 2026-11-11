
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Student Management System')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f3f6fb;
            font-family: Arial, sans-serif;
            color: #1e293b;
        }

        .card {
            border-radius: 1rem;
        }

        .top-navbar {
            min-height: 72px;
            z-index: 1030;
        }

        .sidebar {
            width: 250px;
            height: calc(100vh - 72px);
            top: 72px;
            left: 0;
            overflow-y: auto;
            z-index: 1020;
            transition: transform 180ms ease;
        }

        .main-content {
            margin-left: 250px;
            padding: 90px 25px 25px 12px;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            display: flex;
            min-height: 48px;
            align-items: center;
            border-radius: 0.5rem;
            padding: 0.75rem 0.85rem;
            transition: background-color 150ms ease, color 150ms ease;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .sidebar .nav-link:not(.active):hover {
            background-color: rgb(255 255 255 / 12%);
            color: #fff;
        }

        .dashboard-stats > div {
            min-width: 0;
        }

        /* Student directory styling */
        .directory-card {
            border: 0;
            box-shadow: 0 4px 18px rgb(15 23 42 / 5%);
            overflow: hidden;
        }

        .directory-card .card-header,
        .directory-card .card-footer {
            background: #fff;
        }

        .directory-table {
            white-space: nowrap;
        }

        .directory-table thead th {
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding-top: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .directory-table tbody td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            border-color: #edf0f5;
        }

        .directory-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .student-avatar {
            width: 42px;
            height: 42px;
            min-width: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e8efff;
            color: #3157c8;
            font-weight: 700;
        }

        .student-search {
            min-width: 260px;
        }

        .status-badge,
        .gender-badge {
            border-radius: 50rem;
            padding: 0.45rem 0.75rem;
            font-weight: 600;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }

        .pagination {
            margin-bottom: 0;
            gap: 4px;
        }

        .pagination .page-link {
            border-radius: 0.5rem;
            border: 0;
            color: #475569;
        }

        .pagination .active .page-link {
            background: #0d6efd;
            color: #fff;
        }

        .sidebar-backdrop {
            position: fixed;
            z-index: 1015;
            inset: 72px 0 0;
            background: rgb(15 23 42 / 45%);
        }

        @media (max-width: 767.98px) {
            .sidebar {
                width: min(280px, 85vw);
                transform: translateX(-100%);
            }

            .sidebar.is-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 90px 15px 25px;
            }

            .student-search {
                min-width: 0;
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .main-content {
                padding-right: 10px;
                padding-left: 10px;
            }

            .dashboard-stats .card-body {
                padding: 0.65rem !important;
            }

            .dashboard-stats .card-body > div {
                align-items: flex-start !important;
            }

            .dashboard-stats .card-body p {
                font-size: 0.65rem;
                line-height: 1.2;
            }

            .dashboard-stats .card-body h3 {
                font-size: 0.75rem !important;
                white-space: normal !important;
                overflow-wrap: anywhere;
            }

            .dashboard-stats .card-body > div > div:last-child {
                display: none !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- Top Navigation --}}
    @include('layouts.navigation')

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Mobile Sidebar Backdrop --}}
    <div class="sidebar-backdrop d-none" aria-hidden="true"></div>

    {{-- Main Page Content --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- Bootstrap JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Mobile Sidebar Toggle --}}
    <script>
        (() => {
            const sidebar = document.getElementById('app-sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            const backdrop = document.querySelector('.sidebar-backdrop');

            if (!sidebar || !toggle || !backdrop) {
                return;
            }

            const closeSidebar = () => {
                sidebar.classList.remove('is-open');
                backdrop.classList.add('d-none');
                toggle.setAttribute('aria-expanded', 'false');
            };

            toggle.addEventListener('click', () => {
                const isOpen = sidebar.classList.toggle('is-open');

                backdrop.classList.toggle('d-none', !isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
            });

            backdrop.addEventListener('click', closeSidebar);

            sidebar.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', closeSidebar);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    closeSidebar();
                }
            });
        })();
    </script>

    @stack('scripts')

</body>
</html>

