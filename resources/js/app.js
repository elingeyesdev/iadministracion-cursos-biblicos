import './bootstrap';
import './theme';
import './sidebar';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import 'bootstrap';

// SweetAlert se empaqueta con Vite (antes venía de un CDN que la CSP
// bloqueaba). Las vistas siguen usando `Swal.fire(...)`.
window.Swal = Swal;
window.Alpine = Alpine;

Alpine.start();
