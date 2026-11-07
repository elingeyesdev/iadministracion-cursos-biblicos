{{--
    ReportSummary — indicadores compactos.
    $items: array de ['label' => string, 'value' => mixed, 'tone' => neutral|success|danger|warning|info|muted]
--}}
@props(['items' => []])

@php
    $items = collect($items)->filter(fn ($item) => isset($item['label']));
@endphp

@if($items->isNotEmpty())
    <section class="report-summary" aria-label="Resumen estadístico">
        @foreach($items as $item)
            <div class="report-summary__item report-summary__item--{{ $item['tone'] ?? 'neutral' }}">
                <span class="report-summary__value">{{ $item['value'] ?? 0 }}</span>
                <span class="report-summary__label">{{ $item['label'] }}</span>
            </div>
        @endforeach
    </section>
@endif
