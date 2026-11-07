@props(['items' => []])

{{--
    Resumen estad├¡stico compacto.
    $items: [ ['label' => 'Total', 'value' => 12, 'class' => 'is-success'], ... ]
    Los valores siempre provienen de los datos filtrados reales.
--}}
<div class="report-summary">
    @foreach($items as $item)
        <div class="report-summary-item">
            <div class="report-summary-label">{{ $item['label'] }}</div>
            <div class="report-summary-value {{ $item['class'] ?? '' }}">{{ $item['value'] }}</div>
        </div>
    @endforeach
</div>
