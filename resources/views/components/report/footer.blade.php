{{--
    Pie del documento del reporte.
    El navegador no permite numerar páginas de forma fiable por CSS, así que se
    muestra la fecha de generación; la numeración por página se solicita vía
    las cajas de margen de @page cuando el motor de impresión lo soporta.
--}}
<footer class="report-footer">
    <div class="report-footer-text">
        Sistema Iglesia · Documento generado automáticamente.
    </div>
    <div class="report-footer-meta">
        <span>Generado el {{ now()->format('d/m/Y H:i') }}</span>
    </div>
</footer>
