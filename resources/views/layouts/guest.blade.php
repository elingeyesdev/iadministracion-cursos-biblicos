<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="church-light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('layouts.partials.theme-script')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @include('layouts.partials.theme-toggle', ['class' => 'position-fixed top-0 end-0 m-3'])
        <div class="container-fluid min-vh-100">
            <div class="row min-vh-100">
                <div class="d-none d-lg-flex col-lg-7 position-relative p-5 align-items-end" style="background: var(--church-surface); border-right:1px solid var(--church-border);">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image:repeating-linear-gradient(-45deg,transparent,transparent 36px,rgba(255,255,255,.012) 36px,rgba(255,255,255,.012) 37px)"></div>
                    <div class="position-absolute top-0 start-0 p-5">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4" style="color: var(--church-gold);">Ô£Ø</span>
                            <div class="sidebar-title fs-4" style="color: var(--church-text);">{{ config('app.name') }}</div>
                        </div>
                        <div class="text-muted small mt-1" style="letter-spacing:.18em; text-transform:uppercase;">Bienvenido</div>
                    </div>

                    <div class="position-relative" style="max-width: 38rem;">
                        <blockquote class="sidebar-title fst-italic" style="font-size:2rem; line-height:1.2;">
                            "Sirviendo con orden, creciendo en fe."
                        </blockquote>
                        <div class="text-muted small mt-2" style="letter-spacing:.18em; text-transform:uppercase;">ÔÇö Comunidad</div>
                    </div>
                </div>

                <div class="col-12 col-lg-5 d-flex align-items-center justify-content-center p-4" style="background: var(--church-bg);">
                    <div class="w-100" style="max-width: 420px;">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
