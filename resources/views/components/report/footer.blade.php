{{--
    ReportFooter — pie de página del documento.
    No incluye datos personales del usuario conectado ni información inventada.
--}}
@props([
    'generatedAt' => null,
    'text' => null,
])

@php
    $generatedAt = $generatedAt ? \Carbon\Carbon::parse($generatedAt) : now();
    $text = $text ?? 'Documento generado automáticamente por el Sistema Iglesia Actúa.';
@endphp

<footer class="report-footer">
    <p class="report-footer__text">{{ $text }}</p>
    <p class="report-footer__date">
        Generado el {{ $generatedAt->translatedFormat('d/m/Y') }} a las {{ $generatedAt->format('H:i') }}
    </p>
</footer>
