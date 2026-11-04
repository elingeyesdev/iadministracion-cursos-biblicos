@props(['title', 'subtitle' => null, 'period' => null])

{{--
    Encabezado institucional del documento del reporte.
    La identidad (marca "✝", "Sistema Iglesia" y su descriptor) ya existe en
    el proyecto; no se inventan datos institucionales.
--}}
<header class="report-header">
    <div class="report-header-brand">
        <span class="report-header-logo" aria-hidden="true">✝</span>
        <div>
            <div class="report-header-org">Sistema Iglesia</div>
            <div class="report-header-org-sub">Sistema de Gestión Eclesiástica</div>
        </div>
    </div>

    <div class="report-header-title">
        <h1>{{ $title }}</h1>
        @if($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
        <div class="report-header-meta">
            <div><span>Generado:</span> {{ now()->format('d/m/Y H:i') }}</div>
            @if($period)
                <div><span>Período:</span> {{ $period }}</div>
            @endif
        </div>
    </div>
</header>
