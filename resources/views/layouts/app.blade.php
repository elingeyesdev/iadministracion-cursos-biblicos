<!DOCTYPE html>
<html lang="es" class="church-light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Iglesia') ÔÇö Sistema de Gesti├│n</title>

    @include('layouts.partials.theme-script')
    @include('layouts.partials.sidebar-script')

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Custom styling -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body_class')">
    <div class="d-flex min-vh-100">
        {{-- Desktop sidebar --}}
        <aside class="app-chrome d-none d-lg-block sidebar flex-shrink-0">
            @include('layouts.sidebar')
        </aside>

        {{-- Mobile offcanvas sidebar --}}
        <div class="app-chrome offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
            <div class="offcanvas-body p-0">
                @include('layouts.sidebar')
            </div>
        </div>

        <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
            <header class="app-chrome border-bottom" style="border-color: var(--church-border) !important; background: var(--church-surface);">
                <div class="container-fluid py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-church d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                            <i class="bi bi-list"></i> Men├║
                        </button>
                        <span class="fs-4 fw-bold text-white d-lg-none">Sistema Iglesia</span>
                        <div class="d-none d-lg-block text-muted small fw-semibold" style="letter-spacing:.18em; text-transform:uppercase;">
                            Ôø¬ Sistema de Gesti├│n Eclesi├ística
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        @include('layouts.partials.theme-toggle')
                    </div>
                </div>
            </header>

            <main class="container-fluid py-4 flex-grow-1">
                <!-- Breadcrumb -->
                <div class="app-chrome mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 py-2 px-3 rounded" style="background-color: var(--church-surface); border: 1px solid var(--church-border);">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-gold-500 text-decoration-none"><i class="bi bi-house-door-fill me-1"></i>Inicio</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                </div>

                @yield('content')
            </main>

            <footer class="app-chrome container-fluid py-3 text-center border-top mt-auto" style="border-color: var(--church-border) !important; background: var(--church-surface);">
                <div class="text-muted small">
                    Ô£Ø Sistema de Gesti├│n Eclesi├ística ┬® {{ date('Y') }}
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Success and Error alerts from session -->
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '┬í├ëxito!',
            text: {!! json_encode(session('success')) !!},
            timer: 2500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timerProgressBar: true
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: {!! json_encode(session('error')) !!},
            timer: 3500,
            showConfirmButton: true
        });
    </script>
    @endif

    @yield('scripts')
</body>
</html>
