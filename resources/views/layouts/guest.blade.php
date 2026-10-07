<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Student Management System') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            :root {
                color-scheme: light;
                --login-navy: #142b4a;
                --login-blue: #2563eb;
            }

            body {
                min-height: 100vh;
                margin: 0;
                background:
                    radial-gradient(ellipse at top left, rgb(37 99 235 / 8%), transparent 38rem),
                    #f4f7fc;
                color: #172b4d;
                font-family: Arial, sans-serif;
            }

            .login-shell {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
            }

            .login-logo {
                display: inline-flex;
                width: 2.8rem;
                height: 2.8rem;
                align-items: center;
                justify-content: center;
                flex: 0 0 auto;
                border-radius: 0.85rem;
                background: #eaf1ff;
                color: var(--login-blue);
                font-size: 1.4rem;
            }

            .login-brand {
                color: var(--login-navy);
                font-size: 0.95rem;
                font-weight: 700;
            }

            .login-brand:hover {
                color: var(--login-blue);
            }

            .login-main {
                display: flex;
                align-items: center;
                justify-content: center;
                width: min(100%, 28rem);
                padding: clamp(1.5rem, 4vw, 2.25rem);
                border: 1px solid #e5eaf2;
                border-radius: 1.1rem;
                background: #fff;
                box-shadow: 0 1rem 2.5rem rgb(23 43 77 / 9%);
            }

            .login-card {
                width: 100%;
            }

            .login-card h2 {
                color: var(--login-navy);
                font-size: 1.65rem;
                font-weight: 700;
                letter-spacing: -0.03em;
                line-height: 1.25;
            }

            .login-card .form-label {
                color: #344563;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .login-card .form-control {
                min-height: 2.85rem;
                border-color: #d9e1ee;
                border-radius: 0.65rem;
                padding: 0.65rem 0.85rem;
                font-size: 0.925rem;
            }

            .login-card .form-control:focus {
                border-color: #8bb4ff;
                box-shadow: 0 0 0 0.22rem rgb(37 99 235 / 12%);
            }

            .login-submit {
                min-height: 2.85rem;
                border: 0;
                border-radius: 0.65rem;
                background: var(--login-blue);
                font-weight: 600;
            }

            .login-submit:hover,
            .login-submit:focus {
                background: #1d4ed8;
            }

            .login-link {
                color: var(--login-blue);
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
            }

            .login-link:hover {
                color: #1d4ed8;
                text-decoration: underline;
            }

            @media (max-width: 767.98px) {
                .login-main {
                    padding: 1.5rem;
                }
            }
        </style>
    </head>
    <body>
        {{ $slot }}
    </body>
</html>
