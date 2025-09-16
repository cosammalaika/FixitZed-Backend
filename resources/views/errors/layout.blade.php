<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') | {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="d-flex flex-column min-vh-100">
        <main class="flex-fill d-flex align-items-center justify-content-center py-5">
            <div class="container px-4">
                <div class="mx-auto text-center" style="max-width: 480px;">
                    <div class="mb-4">
                        <span class="display-1 fw-bold text-primary">@yield('code')</span>
                    </div>
                    <h1 class="h3 fw-semibold mb-3">@yield('title')</h1>
                    <p class="text-muted mb-4">@yield('message')</p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ url()->previous() ?: url('/') }}" class="btn btn-outline-secondary">
                            Go Back
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            Go Home
                        </a>
                    </div>
                </div>
            </div>
        </main>
        <footer class="py-3 text-center text-muted small">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </footer>
    </div>

    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
