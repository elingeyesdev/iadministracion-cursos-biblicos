@props(['title', 'subtitle' => null, 'period' => null])

{{--
    Documento del reporte: es lo único que se imprime/exporta.
    En pantalla se muestra como una "hoja"; al imprimir se expande a A4 y la
    interfaz de la aplicación (marcada con .app-chrome y .report-controls) se
    oculta desde resources/css/report.css.
--}}
<div class="report-document">
    <x-report.header :title="$title" :subtitle="$subtitle" :period="$period" />

    {{ $slot }}

    <x-report.footer />
</div>
