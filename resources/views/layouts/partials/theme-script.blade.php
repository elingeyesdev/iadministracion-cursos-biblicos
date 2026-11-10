<script>
    // Aplica el tema guardado antes del primer render para evitar parpadeos.
    // El modo claro es el predeterminado (la clase ya viene en <html>).
    (function () {
        var theme = 'light';
        try {
            theme = localStorage.getItem('church-theme') || 'light';
        } catch (e) {}
        if (theme === 'dark') {
            document.documentElement.classList.remove('church-light');
            document.documentElement.classList.add('church-dark');
        }
    })();
</script>
