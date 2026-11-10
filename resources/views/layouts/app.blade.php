<!DOCTYPE html>
<html lang="es" class="church-light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Iglesia') — Sistema de Gestión</title>

    @include('layouts.partials.theme-script')
    @include('layouts.partials.sidebar-script')

    <!-- Bootstrap se incluye en app.css/app.js mediante Vite. -->

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
                            <i class="bi bi-list"></i> Menú
                        </button>
                        <span class="fs-4 fw-bold text-white d-lg-none">Sistema Iglesia</span>
                        <div class="d-none d-lg-block text-muted small fw-semibold" style="letter-spacing:.18em; text-transform:uppercase;">
                            ⛪ Sistema de Gestión Eclesiástica
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
                    ✝ Sistema de Gestión Eclesiástica © {{ date('Y') }}
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap y SweetAlert2 se incluyen en app.js mediante Vite. -->

    <!-- Success and Error alerts from session -->
    @if(session('success'))
    <script>
        // SweetAlert viene en app.js (módulo diferido), así que esperamos a que
        // el documento termine de cargar para que window.Swal ya exista.
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: {!! json_encode(session('success')) !!},
                timer: 2500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                timerProgressBar: true
            });
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: {!! json_encode(session('error')) !!},
                timer: 3500,
                showConfirmButton: true
            });
        });
    </script>
    @endif

    @yield('scripts')
</body>
</html>
