{{--
    ReportHeader — encabezado institucional del documento.
    Solo muestra datos reales: el nombre institucional existe en el proyecto
    (vistas de autenticación y layout) y no se inventan logotipos ni direcciones.
--}}
@props([
    'title' => 'Reporte',
    'subtitle' => null,
    'generatedAt' => null,
    'period' => null,
    'meta' => [],
    'institution' => 'Iglesia Actúa',
])

@php
    $generatedAt = $generatedAt ? \Carbon\Carbon::parse($generatedAt) : now();
    $meta = array_filter((array) $meta, fn ($value) => $value !== null && $value !== '');
@endphp

<header class="report-header">
    <div class="report-header__identity">
        <p class="report-header__institution">{{ $institution }}</p>
        <h1 class="report-header__title">{{ $title }}</h1>
        @if($subtitle)
            <p class="report-header__subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="report-header__meta">
        <div class="report-header__meta-item">
            <span class="report-header__meta-label">Generado</span>
            <span class="report-header__meta-value">{{ $generatedAt->translatedFormat('d/m/Y H:i') }}</span>
        </div>

        @if($period)
            <div class="report-header__meta-item">
                <span class="report-header__meta-label">Período consultado</span>
                <span class="report-header__meta-value">{{ $period }}</span>
            </div>
        @endif

        @foreach($meta as $label => $value)
            <div class="report-header__meta-item">
                <span class="report-header__meta-label">{{ $label }}</span>
                <span class="report-header__meta-value">{{ $value }}</span>
            </div>
        @endforeach
    </div>
</header>
