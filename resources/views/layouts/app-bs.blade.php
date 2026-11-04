<!DOCTYPE html>
<html lang="es" class="church-light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Iglesia') — Sistema de Gestión</title>
    @include('layouts.partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="d-flex min-vh-100">
        {{-- Desktop sidebar --}}
        <aside class="d-none d-lg-block sidebar">
            @include('layouts.sidebar')
        </aside>

        {{-- Mobile offcanvas sidebar --}}
        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
            <div class="offcanvas-body p-0">
                @include('layouts.sidebar')
            </div>
        </div>

        <div class="flex-grow-1">
            <header class="border-bottom" style="border-color: var(--church-border) !important; background: var(--church-surface);">
                <div class="container-fluid py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-church d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                            Menú
                        </button>
                        <div class="d-none d-lg-block text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">
                            Sistema de gestión eclesiástica
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @include('layouts.partials.theme-toggle')
                        <div class="text-muted small d-none d-sm-block">
                            {{ auth()->user()->email ?? '' }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="container-fluid py-4">
                @if(session('success'))
                    <div class="alert alert-success border-0" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="container-fluid pb-4 text-center">
                <div class="text-muted small">
                    ✝ Sistema de Gestión
                </div>
            </footer>
        </div>
    </div>
</body>
</html>

