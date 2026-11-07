{{--
    ReportTable — tabla profesional reutilizable.

    $columns: array de ['label' => string, 'align' => left|center|right, 'width' => css]
    $rows:    array de arrays. Cada celda puede ser:
              - escalar (se escapa como texto)
              - ['badge' => string, 'tone' => neutral|success|danger|warning|info|muted]
              - ['text' => string]
--}}
@props([
    'columns' => [],
    'rows' => [],
    'empty' => 'No hay registros para mostrar.',
    'caption' => null,
])

@php
    $columns = array_values((array) $columns);
    $rows = $rows instanceof \Illuminate\Support\Collection ? $rows->all() : (array) $rows;
@endphp

<section class="report-block">
    @if($caption)
        <h2 class="report-block__title">{{ $caption }}</h2>
    @endif

    @if(count($rows) === 0)
        <p class="report-empty">{{ $empty }}</p>
    @else
        {{-- El scroll horizontal (si la tabla lo necesitara) queda contenido aquí:
             nunca desplaza ni desborda el resto de la vista previa. --}}
        <div class="report-table-scroll">
            <table class="report-table">
                <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th @class(['is-center' => ($column['align'] ?? null) === 'center', 'is-right' => ($column['align'] ?? null) === 'right'])
                                @if(!empty($column['width'])) style="width: {{ $column['width'] }}" @endif>
                                {{ $column['label'] ?? '' }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            @foreach($columns as $index => $column)
                                @php $cell = $row[$index] ?? ''; @endphp
                                <td @class(['is-center' => ($column['align'] ?? null) === 'center', 'is-right' => ($column['align'] ?? null) === 'right'])>
                                    @if(is_array($cell) && isset($cell['badge']))
                                        <span class="report-badge report-badge--{{ $cell['tone'] ?? 'neutral' }}">{{ $cell['badge'] }}</span>
                                    @elseif(is_array($cell))
                                        {{ $cell['text'] ?? '' }}
                                    @else
                                        {{ $cell }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
