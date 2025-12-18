<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Locked | {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
</head>

<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            @php($photo = $user?->profile_photo_path)
                            <span
                                class="avatar avatar-xxl rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center overflow-hidden">
                                @if ($photo)
                                    <img src="{{ Storage::disk('public')->url($photo) }}" alt="avatar"
                                        class="rounded-circle" height="96" width="96" style="object-fit: cover;">
                                @else
                                    <span class="fw-semibold text-primary fs-3">
                                        {{ strtoupper(substr($user->first_name ?? ($user->name ?? 'U'), 0, 1)) }}
                                    </span>
                                @endif
                            </span>
                        </div>
                        <h4 class="fw-semibold mb-1">{{ $user->first_name }} {{ $user->last_name }}</h4>
                        <p class="text-muted">Your session is locked</p>
                        <p class="text-muted small mb-4">Enter your password to continue using {{ config('app.name') }}.
                        </p>

                        <form method="POST" action="{{ route('lock.unlock') }}" class="text-start">
                            @csrf
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="mdi mdi-lock"></i></span>
                                    <input type="password" id="password" name="password" class="form-control" required
                                        autofocus>
                                </div>
                                @error('password')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Unlock</button>
                        </form>

                        <div class="mt-4">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link text-decoration-none text-muted">
                                    <i class="mdi mdi-logout me-1"></i> Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
