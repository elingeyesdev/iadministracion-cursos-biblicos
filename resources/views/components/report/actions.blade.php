{{--
    PrintActions — botones de acción del reporte (interfaz, nunca se imprime).
    Ejecuta acciones reales: vista previa, impresión y exportación a PDF
    mediante el diálogo de impresión del navegador (elegir "Guardar como PDF").
--}}
@props([
    'backRoute' => null,
    'backLabel' => '← Volver a Reportes',
])

<div class="report-actions">
    @if($backRoute)
        <a href="{{ $backRoute }}" class="report-btn report-btn--ghost">{{ $backLabel }}</a>
    @else
        <span></span>
    @endif

    <div class="report-actions__group">
        <button type="button" class="report-btn report-btn--ghost" data-report-preview>Vista previa</button>
        <button type="button" class="report-btn" data-report-print>Imprimir</button>
        <button type="button" class="report-btn report-btn--primary" data-report-pdf>Exportar PDF</button>
    </div>
</div>

<button type="button" class="report-preview-exit" data-report-preview-exit hidden>Cerrar vista previa</button>
<output class="report-toast" data-report-toast aria-live="polite"></output>

@push('scripts')
<script>
    (function () {
        const body = document.body;
        const exitButton = document.querySelector('[data-report-preview-exit]');
        const toast = document.querySelector('[data-report-toast]');
        let toastTimer = null;

        function showToast(message) {
            if (!toast) return;
            toast.textContent = message;
            toast.classList.add('is-visible');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 4200);
        }

        function setPreview(enabled) {
            body.classList.toggle('report-preview-mode', enabled);
            if (exitButton) exitButton.hidden = !enabled;
        }

        document.querySelectorAll('[data-report-preview]').forEach((button) => {
            button.addEventListener('click', () => setPreview(true));
        });

        document.querySelectorAll('[data-report-preview-exit]').forEach((button) => {
            button.addEventListener('click', () => setPreview(false));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setPreview(false);
        });

        // Ambos botones abren el diálogo de impresión optimizado: desde allí
        // se puede imprimir en papel o elegir "Guardar como PDF".
        document.querySelectorAll('[data-report-print]').forEach((button) => {
            button.addEventListener('click', () => {
                showToast('Abriendo el diálogo de impresión…');
                window.print();
            });
        });

        document.querySelectorAll('[data-report-pdf]').forEach((button) => {
            button.addEventListener('click', () => {
                showToast('En el diálogo de impresión elige "Guardar como PDF" como destino.');
                window.print();
            });
        });
    })();
</script>
@endpush
