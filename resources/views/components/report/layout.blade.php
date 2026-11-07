{{--
    ReportLayout
    Envoltura del DOCUMENTO imprimible. Todo lo que esté dentro se imprime;
    todo lo que esté fuera (panel de control, acciones) se oculta con @media print.
--}}
@props([
    'title' => 'Reporte',
    'subtitle' => null,
    'generatedAt' => null,
    'period' => null,
    'meta' => [],
    'footerText' => null,
])

@php
    $generatedAt = $generatedAt ? \Carbon\Carbon::parse($generatedAt) : now();
@endphp

<article class="report-document" id="report-document" aria-label="{{ $title }}">
    <x-report.header
        :title="$title"
        :subtitle="$subtitle"
        :generated-at="$generatedAt"
        :period="$period"
        :meta="$meta"
    />

    {{ $summary ?? '' }}

    <div class="report-body">
        {{ $slot }}
    </div>

    <x-report.footer :generated-at="$generatedAt" :text="$footerText" />
</article>
