@extends('layouts.app')

@section('title', 'Buscador b├¡blico')

@section('content')
<div class="mb-4">
    <h1 class="sidebar-title" style="font-size:2rem;">Buscador b├¡blico</h1>
    <p class="text-muted mb-0">Busca referencias b├¡blicas en espa├▒ol (Reina-Valera 1909).</p>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="search-form" class="row g-3">
                    <div class="col-12 col-md-9">
                        <label for="query" class="form-label">Referencia b├¡blica</label>
                        <input id="query" name="q" type="text" class="form-control" placeholder="Ej: Juan 3:16, Salmos 23 o 1 Corintios 13:4-7" autocomplete="off">
                    </div>
                    <div class="col-12 col-md-3 d-grid align-self-end">
                        <button id="search-button" type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>

                <div id="search-feedback" class="text-muted small mt-3">Escribe una referencia y presiona Buscar. Tambi├®n se busca autom├íticamente mientras escribes.</div>
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
    const feedback = document.querySelector('#search-feedback');
    const resultsContainer = document.querySelector('#search-results');

    let debounceTimeout = null;

    const setFeedback = (message, type = 'text-muted') => {
        feedback.textContent = message;
        feedback.className = `small mt-3 ${type}`;
    };

    const renderError = (message) => {
        resultsContainer.innerHTML = `
            <div class="alert alert-warning" role="alert">
                ${message}
            </div>
        `;
    };

    const renderResult = (data) => {
        const reference = data.reference || 'Referencia no encontrada';
        const translation = data.translation_name ? ` (${data.translation_name})` : '';
        const verses = Array.isArray(data.verses) ? data.verses : [];
        const text = data.text || verses.map(v => `${v.verse}. ${v.text}`).join('');

        resultsContainer.innerHTML = `
            <div class="border rounded p-4 bg-white shadow-sm">
                <div class="mb-3">
                    <div class="text-muted small">Referencia</div>
                    <h2 class="h5">${reference}${translation}</h2>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Texto</div>
                    <div class="fs-6" style="white-space: pre-wrap;">${escapeHtml(text)}</div>
                </div>
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

    const searchBible = async (query) => {
        const trimmed = query.trim();
        if (!trimmed) {
            setFeedback('Escribe una referencia para buscar.');
            resultsContainer.innerHTML = '';
            return;
        }

        setFeedback('Buscando vers├¡culos...', 'text-primary');
        resultsContainer.innerHTML = '';

        try {
            const url = `/biblia/buscar/versiculos?q=${encodeURIComponent(trimmed)}`;
            const response = await fetch(url);
            const data = await response.json();

            if (!response.ok) {
                renderError(data.error || 'No se encontr├│ el vers├¡culo.');
                setFeedback('Error al buscar la referencia.', 'text-danger');
                return;
            }

            renderResult(data);
            setFeedback('Vers├¡culo cargado correctamente.', 'text-success');
        } catch (error) {
            renderError('Error al conectar con la API.');
            setFeedback('No se pudo conectar con Bible API.', 'text-danger');
        }
    };

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        searchBible(queryInput.value);
    });

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
