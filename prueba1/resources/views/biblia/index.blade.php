@extends('layouts.app')

@section('title', 'Biblia')

@section('content')
<div class="mb-4">
    <h1 class="sidebar-title" style="font-size:2rem;">Buscador bíblico</h1>
    <p class="text-muted mb-0">Busca versículos usando la API pública de Bible API.</p>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="search-form" class="row g-3">
                    <div class="col-12">
                        <label for="query" class="form-label">Referencia bíblica</label>
                        <input id="query" name="q" type="text" class="form-control" placeholder="Ej: John 3:16 o Mateo 5:9" autocomplete="off">
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>

                <div id="search-feedback" class="text-muted small mt-3">Escribe una referencia y presiona Buscar.</div>
                <div id="search-results" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const searchForm = document.querySelector('#search-form');
    const queryInput = document.querySelector('#query');
    const searchFeedback = document.querySelector('#search-feedback');
    const searchResults = document.querySelector('#search-results');

    const setFeedback = (message, type = 'text-muted') => {
        searchFeedback.textContent = message;
        searchFeedback.className = `small mt-3 ${type}`;
    };

    const renderSearchError = (message) => {
        searchResults.innerHTML = `
            <div class="alert alert-warning" role="alert">
                ${message}
            </div>
        `;
    };

    const escapeHtml = (unsafe) => {
        return unsafe
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const renderSearchResult = (data) => {
        const reference = data.reference || 'Referencia no encontrada';
        const translation = data.translation_name ? ` (${data.translation_name})` : '';
        const text = data.text || (Array.isArray(data.verses) ? data.verses.map(v => `${v.verse}. ${v.text}`).join('') : '');

        searchResults.innerHTML = `
            <div class="border rounded p-4 bg-white shadow-sm">
                <div class="mb-3">
                    <div class="text-muted small">Referencia</div>
                    <h2 class="h5">${escapeHtml(reference)}${escapeHtml(translation)}</h2>
                </div>
                <div>
                    <div class="text-muted small">Texto</div>
                    <div class="fs-6" style="white-space: pre-wrap;">${escapeHtml(text)}</div>
                </div>
            </div>
        `;
    };

    const searchBible = async (query) => {
        const trimmed = query.trim();
        if (!trimmed) {
            setFeedback('Escribe una referencia para buscar.');
            searchResults.innerHTML = '';
            return;
        }

        setFeedback('Buscando versículos...', 'text-primary');
        searchResults.innerHTML = '';

        try {
            const response = await fetch(`/biblia/buscar/versiculos?q=${encodeURIComponent(trimmed)}`);
            const data = await response.json();

            if (!response.ok) {
                renderSearchError(data.error || 'No se encontró el versículo.');
                setFeedback('Error al buscar la referencia.', 'text-danger');
                return;
            }

            renderSearchResult(data);
            setFeedback('Versículo cargado correctamente.', 'text-success');
        } catch (error) {
            renderSearchError('Error al conectar con la API.');
            setFeedback('No se pudo conectar con Bible API.', 'text-danger');
        }
    };

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        searchBible(queryInput.value);
    });

    let debounceTimeout = null;
    queryInput.addEventListener('input', () => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            if (queryInput.value.trim().length > 2) {
                searchBible(queryInput.value);
            }
        }, 600);
    });
</script>
@endsection
