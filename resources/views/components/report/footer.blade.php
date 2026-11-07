{{--
    Pie del documento del reporte.
    El navegador no permite numerar p├íginas de forma fiable por CSS, as├¡ que se
    muestra la fecha de generaci├│n; la numeraci├│n por p├ígina se solicita v├¡a
    las cajas de margen de @page cuando el motor de impresi├│n lo soporta.
--}}
<footer class="report-footer">
    <div class="report-footer-text">
        Sistema Iglesia ┬À Documento generado autom├íticamente.
    </div>
    <div class="report-footer-meta">
        <span>Generado el {{ now()->format('d/m/Y H:i') }}</span>
    </div>
</footer>
