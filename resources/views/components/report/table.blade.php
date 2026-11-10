@props(['columns' => []])

{{--
    Tabla profesional reutilizable.
    $columns: array de etiquetas o de arreglos ['label' => '...', 'class' => 'num|center'].
    Las filas se pasan mediante el slot por defecto.
--}}
<div class="report-table-wrapper">
    <table class="report-table">
        <thead>
            <tr>
                @foreach($columns as $column)
                    @php
                        $label = is_array($column) ? ($column['label'] ?? '') : $column;
                        $class = is_array($column) ? ($column['class'] ?? '') : '';
                    @endphp
                    <th @if($class) class="{{ $class }}" @endif>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
