@props([])

{{--
    Acciones de la interfaz (no forman parte del documento imprimible):
    - "Aplicar filtros" envía el formulario de filtros que lo contiene.
    - "Imprimir / Guardar PDF" abre el diálogo de impresión del navegador,
      que ya incluye la vista previa y permite guardar como PDF.
      No requiere dependencias nuevas.
--}}
<div {{ $attributes->class(['report-actions']) }}>
    <button type="submit" class="btn btn-church">
        <i class="bi bi-funnel me-1"></i> Aplicar filtros
    </button>
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Imprimir / Guardar PDF
    </button>
</div>
