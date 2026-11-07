<script>
    // Restaura el estado del men├║ lateral (expandido/contra├¡do) antes del
    // primer render para evitar parpadeos. Solo afecta a escritorio: el CSS
    // limita el estado con una media query. Ver resources/js/sidebar.js.
    (function () {
        try {
            if (localStorage.getItem('church-sidebar') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    })();
</script>
