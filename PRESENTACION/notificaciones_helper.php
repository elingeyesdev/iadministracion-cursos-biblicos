<?php
require_once '../NEGOCIO/auth.php';

/**
 * Genera el HTML del botón de notificaciones para el navbar
 * Se muestra si el usuario tiene privilegio para crear docentes, miembros o estudiantes
 */
function generarBotonNotificacionesNavbar() {
    $tieneDocentes = auth::tienePrivilegio('docentes_crear');
    $tieneMiembros = auth::tienePrivilegio('miembros_crear');
    $tieneEstudiantes = auth::tienePrivilegio('estudiantes_crear');
    
    if (!$tieneDocentes && !$tieneMiembros && !$tieneEstudiantes) {
        return '';
    }
    
    $html = '
    <button type="button" class="btn btn-outline-primary btn-notificaciones-navbar position-relative" id="btnNotificacionesNavbar" title="Ver notificaciones">
        <i class="fas fa-bell"></i>
        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="badgeNotificacionesNavbar" style="display: none;">0</span>
    </button>
    ';
    
    return $html;
}

/**
 * Genera el HTML del modal de notificaciones y contenedor toast
 * Se muestra si el usuario tiene privilegio para crear docentes, miembros o estudiantes
 */
function generarNotificacionesDocentes() {
    $tieneDocentes = auth::tienePrivilegio('docentes_crear');
    $tieneMiembros = auth::tienePrivilegio('miembros_crear');
    $tieneEstudiantes = auth::tienePrivilegio('estudiantes_crear');
    
    if (!$tieneDocentes && !$tieneMiembros && !$tieneEstudiantes) {
        return '';
    }
    
    $html = '
    <!-- Modal de Notificaciones -->
    <div class="modal fade" id="modalNotificaciones" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-bell"></i> Notificaciones de Solicitudes
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="tabsNotificaciones" role="tablist">
                        ' . ($tieneDocentes ? '<li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-docentes" data-bs-toggle="tab" data-bs-target="#panel-docentes" type="button" role="tab">
                                <i class="fas fa-chalkboard-teacher"></i> Docentes
                                <span class="badge bg-danger ms-1" id="badge-docentes">0</span>
                            </button>
                        </li>' : '') . '
                        ' . ($tieneMiembros ? '<li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-miembros" data-bs-toggle="tab" data-bs-target="#panel-miembros" type="button" role="tab">
                                <i class="fas fa-users"></i> Miembros
                                <span class="badge bg-danger ms-1" id="badge-miembros">0</span>
                            </button>
                        </li>' : '') . '
                        ' . ($tieneEstudiantes ? '<li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-estudiantes" data-bs-toggle="tab" data-bs-target="#panel-estudiantes" type="button" role="tab">
                                <i class="fas fa-user-graduate"></i> Estudiantes
                                <span class="badge bg-danger ms-1" id="badge-estudiantes">0</span>
                            </button>
                        </li>' : '') . '
                    </ul>
                    <div class="tab-content" id="contenidoNotificaciones">
                        ' . ($tieneDocentes ? '<div class="tab-pane fade show active" id="panel-docentes" role="tabpanel">
                            <div id="listaNotificacionesDocentes">
                                <p class="text-muted text-center">Cargando notificaciones...</p>
                            </div>
                        </div>' : '') . '
                        ' . ($tieneMiembros ? '<div class="tab-pane fade" id="panel-miembros" role="tabpanel">
                            <div id="listaNotificacionesMiembros">
                                <p class="text-muted text-center">Cargando notificaciones...</p>
                            </div>
                        </div>' : '') . '
                        ' . ($tieneEstudiantes ? '<div class="tab-pane fade" id="panel-estudiantes" role="tabpanel">
                            <div id="listaNotificacionesEstudiantes">
                                <p class="text-muted text-center">Cargando notificaciones...</p>
                            </div>
                        </div>' : '') . '
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenedor para notificaciones toast -->
    <div class="toast-container" id="toastContainer"></div>
    ';
    
    return $html;
}

/**
 * Genera los estilos CSS para las notificaciones
 */
function generarEstilosNotificaciones() {
    return '
    <style>
        /* Estilos para notificaciones toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast-notification {
            min-width: 300px;
            max-width: 400px;
            margin-bottom: 10px;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Botón de notificaciones en el navbar */
        .btn-notificaciones-navbar {
            margin-left: auto;
            position: relative;
        }
        
        .btn-notificaciones-navbar .badge {
            font-size: 0.7rem;
            padding: 4px 6px;
            min-width: 20px;
        }
    </style>
    ';
}

/**
 * Genera el JavaScript para las notificaciones
 */
function generarJavaScriptNotificaciones() {
    $tieneDocentes = auth::tienePrivilegio('docentes_crear');
    $tieneMiembros = auth::tienePrivilegio('miembros_crear');
    $tieneEstudiantes = auth::tienePrivilegio('estudiantes_crear');
    
    if (!$tieneDocentes && !$tieneMiembros && !$tieneEstudiantes) {
        return '';
    }
    
    return '
    <script>
        (function() {
            let ultimoTotalDocentes = 0;
            let ultimoTotalMiembros = 0;
            let ultimoTotalEstudiantes = 0;
            
            // Función para mostrar notificación toast
            window.mostrarNotificacionToast = function(mensaje, tipo = "info") {
                const toastContainer = document.getElementById("toastContainer");
                if (!toastContainer) return;
                
                const tipos = {
                    "info": { bg: "bg-primary", icon: "fa-info-circle" },
                    "success": { bg: "bg-success", icon: "fa-check-circle" },
                    "warning": { bg: "bg-warning", icon: "fa-exclamation-triangle" },
                    "danger": { bg: "bg-danger", icon: "fa-times-circle" }
                };
                
                const tipoConfig = tipos[tipo] || tipos.info;
                const toastId = "toast-" + Date.now();
                
                const toastHTML = `
                    <div class="toast toast-notification ${tipoConfig.bg} text-white" role="alert" id="${toastId}" data-bs-autohide="true" data-bs-delay="5000">
                        <div class="toast-header ${tipoConfig.bg} text-white">
                            <i class="fas ${tipoConfig.icon} me-2"></i>
                            <strong class="me-auto">Notificación</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            ${mensaje}
                        </div>
                    </div>
                `;
                
                toastContainer.insertAdjacentHTML("beforeend", toastHTML);
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement);
                toast.show();
                
                toastElement.addEventListener("hidden.bs.toast", function() {
                    toastElement.remove();
                });
            };
            
            // Función para actualizar el badge del botón de notificaciones
            function actualizarBadgeNotificaciones() {
                const badge = document.getElementById("badgeNotificacionesNavbar");
                if (!badge) return;
                
                let totalDocentes = 0, totalMiembros = 0, totalEstudiantes = 0;
                
                ' . ($tieneDocentes ? '
                const badgeDocentes = document.getElementById("badge-docentes");
                if (badgeDocentes) totalDocentes = parseInt(badgeDocentes.textContent) || 0;
                ' : '') . '
                
                ' . ($tieneMiembros ? '
                const badgeMiembros = document.getElementById("badge-miembros");
                if (badgeMiembros) totalMiembros = parseInt(badgeMiembros.textContent) || 0;
                ' : '') . '
                
                ' . ($tieneEstudiantes ? '
                const badgeEstudiantes = document.getElementById("badge-estudiantes");
                if (badgeEstudiantes) totalEstudiantes = parseInt(badgeEstudiantes.textContent) || 0;
                ' : '') . '
                
                const total = totalDocentes + totalMiembros + totalEstudiantes;
                if (total > 0) {
                    badge.textContent = total;
                    badge.style.display = "block";
                } else {
                    badge.style.display = "none";
                }
            }
            
            // Función para cargar notificaciones de docentes
            ' . ($tieneDocentes ? 'function cargarNotificacionesDocentes() {
                fetch("ajax_notificaciones_docentes.php?accion=contar")
                    .then(response => response.json())
                    .then(data => {
                        if (data.exito) {
                            const total = data.total || 0;
                            const badge = document.getElementById("badge-docentes");
                            if (badge) {
                                badge.textContent = total;
                                badge.style.display = total > 0 ? "inline-block" : "none";
                            }
                            
                            if (total > ultimoTotalDocentes && ultimoTotalDocentes > 0) {
                                const nuevas = total - ultimoTotalDocentes;
                                mostrarNotificacionToast(
                                    `Tienes ${nuevas} nueva${nuevas > 1 ? "s" : ""} solicitud${nuevas > 1 ? "es" : ""} de docente${nuevas > 1 ? "s" : ""} pendiente${nuevas > 1 ? "s" : ""}`,
                                    "info"
                                );
                            }
                            
                            ultimoTotalDocentes = total;
                            actualizarBadgeNotificaciones();
                        }
                    })
                    .catch(error => console.error("Error al cargar notificaciones de docentes:", error));
            }' : '') . '
            
            // Función para cargar notificaciones de miembros
            ' . ($tieneMiembros ? 'function cargarNotificacionesMiembros() {
                fetch("ajax_notificaciones_miembros.php?accion=contar")
                    .then(response => response.json())
                    .then(data => {
                        if (data.exito) {
                            const total = data.total || 0;
                            const badge = document.getElementById("badge-miembros");
                            if (badge) {
                                badge.textContent = total;
                                badge.style.display = total > 0 ? "inline-block" : "none";
                            }
                            
                            if (total > ultimoTotalMiembros && ultimoTotalMiembros > 0) {
                                const nuevas = total - ultimoTotalMiembros;
                                mostrarNotificacionToast(
                                    `Tienes ${nuevas} nueva${nuevas > 1 ? "s" : ""} solicitud${nuevas > 1 ? "es" : ""} de miembro${nuevas > 1 ? "s" : ""} pendiente${nuevas > 1 ? "s" : ""}`,
                                    "info"
                                );
                            }
                            
                            ultimoTotalMiembros = total;
                            actualizarBadgeNotificaciones();
                        }
                    })
                    .catch(error => console.error("Error al cargar notificaciones de miembros:", error));
            }' : '') . '
            
            // Función para cargar notificaciones de estudiantes
            ' . ($tieneEstudiantes ? 'function cargarNotificacionesEstudiantes() {
                fetch("ajax_notificaciones_estudiantes.php?accion=contar")
                    .then(response => response.json())
                    .then(data => {
                        if (data.exito) {
                            const total = data.total || 0;
                            const badge = document.getElementById("badge-estudiantes");
                            if (badge) {
                                badge.textContent = total;
                                badge.style.display = total > 0 ? "inline-block" : "none";
                            }
                            
                            if (total > ultimoTotalEstudiantes && ultimoTotalEstudiantes > 0) {
                                const nuevas = total - ultimoTotalEstudiantes;
                                mostrarNotificacionToast(
                                    `Tienes ${nuevas} nueva${nuevas > 1 ? "s" : ""} solicitud${nuevas > 1 ? "es" : ""} de estudiante${nuevas > 1 ? "s" : ""} pendiente${nuevas > 1 ? "s" : ""}`,
                                    "info"
                                );
                            }
                            
                            ultimoTotalEstudiantes = total;
                            actualizarBadgeNotificaciones();
                        }
                    })
                    .catch(error => console.error("Error al cargar notificaciones de estudiantes:", error));
            }' : '') . '
            
            // Función para cargar todas las notificaciones
            function cargarTodasLasNotificaciones() {
                ' . ($tieneDocentes ? 'cargarNotificacionesDocentes();' : '') . '
                ' . ($tieneMiembros ? 'cargarNotificacionesMiembros();' : '') . '
                ' . ($tieneEstudiantes ? 'cargarNotificacionesEstudiantes();' : '') . '
            }
            
            // Función para cargar lista de notificaciones de docentes en el modal
            ' . ($tieneDocentes ? 'function cargarListaNotificacionesDocentes() {
                fetch("ajax_notificaciones_docentes.php?accion=obtener")
                    .then(response => response.json())
                    .then(data => {
                        const lista = document.getElementById("listaNotificacionesDocentes");
                        if (!lista) return;
                        
                        if (data.exito && data.solicitudes && data.solicitudes.length > 0) {
                            let html = "<div class=\"list-group\">";
                            data.solicitudes.forEach(solicitud => {
                                const fecha = new Date(solicitud.fecha_creacion).toLocaleString("es-ES");
                                html += `
                                    <div class="list-group-item" id="solicitud-docente-${solicitud.id}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-chalkboard-teacher text-primary"></i> 
                                                    ${solicitud.nombres} ${solicitud.apellidos}
                                                </h6>
                                                <p class="mb-1 text-muted small">
                                                    ${solicitud.especialidad ? "<strong>Especialidad:</strong> " + solicitud.especialidad + "<br>" : ""}
                                                    ${solicitud.email ? "<strong>Email:</strong> " + solicitud.email + "<br>" : ""}
                                                    ${solicitud.telefono ? "<strong>Teléfono:</strong> " + solicitud.telefono + "<br>" : ""}
                                                    ${solicitud.direccion ? "<strong>Dirección:</strong> " + solicitud.direccion + "<br>" : ""}
                                                    <small class="text-muted"><i class="fas fa-clock"></i> ${fecha}</small>
                                                </p>
                                            </div>
                                            <div class="btn-group ms-2">
                                                <button class="btn btn-sm btn-success btnAprobarSolicitudDocente" 
                                                        data-solicitud-id="${solicitud.id}"
                                                        title="Aprobar solicitud">
                                                    <i class="fas fa-check"></i> Aprobar
                                                </button>
                                                <button class="btn btn-sm btn-danger btnRechazarSolicitudDocente" 
                                                        data-solicitud-id="${solicitud.id}"
                                                        title="Rechazar solicitud">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            html += "</div>";
                            lista.innerHTML = html;
                            
                            // Agregar event listeners
                            lista.querySelectorAll(".btnAprobarSolicitudDocente").forEach(btn => {
                                btn.addEventListener("click", function() {
                                    const solicitudId = parseInt(this.getAttribute("data-solicitud-id"));
                                    if (window.abrirModalConSolicitudDocente) {
                                        window.abrirModalConSolicitudDocente(solicitudId);
                                    } else {
                                        window.location.href = "docentes.php?notificacion=" + solicitudId;
                                    }
                                });
                            });
                            
                            lista.querySelectorAll(".btnRechazarSolicitudDocente").forEach(btn => {
                                btn.addEventListener("click", function() {
                                    rechazarSolicitudDocente(parseInt(this.getAttribute("data-solicitud-id")));
                                });
                            });
                        } else {
                            lista.innerHTML = "<p class=\"text-muted text-center mb-0\">No hay solicitudes pendientes</p>";
                        }
                    })
                    .catch(error => {
                        console.error("Error al cargar lista de notificaciones de docentes:", error);
                        const lista = document.getElementById("listaNotificacionesDocentes");
                        if (lista) {
                            lista.innerHTML = "<p class=\"text-danger text-center\">Error al cargar notificaciones</p>";
                        }
                    });
            }' : '') . '
            
            // Función para cargar lista de notificaciones de miembros en el modal
            ' . ($tieneMiembros ? 'function cargarListaNotificacionesMiembros() {
                fetch("ajax_notificaciones_miembros.php?accion=obtener")
                    .then(response => response.json())
                    .then(data => {
                        const lista = document.getElementById("listaNotificacionesMiembros");
                        if (!lista) return;
                        
                        if (data.exito && data.solicitudes && data.solicitudes.length > 0) {
                            let html = "<div class=\"list-group\">";
                            data.solicitudes.forEach(solicitud => {
                                const fecha = new Date(solicitud.fecha_creacion).toLocaleString("es-ES");
                                html += `
                                    <div class="list-group-item" id="solicitud-miembro-${solicitud.id}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-users text-primary"></i> 
                                                    ${solicitud.nombres} ${solicitud.apellidos}
                                                </h6>
                                                <p class="mb-1 text-muted small">
                                                    ${solicitud.genero ? "<strong>Género:</strong> " + (solicitud.genero == "M" ? "Masculino" : "Femenino") + "<br>" : ""}
                                                    ${solicitud.email ? "<strong>Email:</strong> " + solicitud.email + "<br>" : ""}
                                                    ${solicitud.telefono ? "<strong>Teléfono:</strong> " + solicitud.telefono + "<br>" : ""}
                                                    ${solicitud.direccion ? "<strong>Dirección:</strong> " + solicitud.direccion + "<br>" : ""}
                                                    <small class="text-muted"><i class="fas fa-clock"></i> ${fecha}</small>
                                                </p>
                                            </div>
                                            <div class="btn-group ms-2">
                                                <button class="btn btn-sm btn-success btnAprobarSolicitudMiembro" 
                                                        data-solicitud-id="${solicitud.id}"
                                                        title="Aprobar solicitud">
                                                    <i class="fas fa-check"></i> Aprobar
                                                </button>
                                                <button class="btn btn-sm btn-danger btnRechazarSolicitudMiembro" 
                                                        data-solicitud-id="${solicitud.id}"
                                                        title="Rechazar solicitud">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            html += "</div>";
                            lista.innerHTML = html;
                            
                            // Agregar event listeners
                            lista.querySelectorAll(".btnAprobarSolicitudMiembro").forEach(btn => {
                                btn.addEventListener("click", function() {
                                    const solicitudId = parseInt(this.getAttribute("data-solicitud-id"));
                                    if (window.abrirModalConSolicitudMiembro) {
                                        window.abrirModalConSolicitudMiembro(solicitudId);
                                    } else {
                                        window.location.href = "miembros.php?notificacion=" + solicitudId;
                                    }
                                });
                            });
                            
                            lista.querySelectorAll(".btnRechazarSolicitudMiembro").forEach(btn => {
                                btn.addEventListener("click", function() {
                                    rechazarSolicitudMiembro(parseInt(this.getAttribute("data-solicitud-id")));
                                });
                            });
                        } else {
                            lista.innerHTML = "<p class=\"text-muted text-center mb-0\">No hay solicitudes pendientes</p>";
                        }
                    })
                    .catch(error => {
                        console.error("Error al cargar lista de notificaciones de miembros:", error);
                        const lista = document.getElementById("listaNotificacionesMiembros");
                        if (lista) {
                            lista.innerHTML = "<p class=\"text-danger text-center\">Error al cargar notificaciones</p>";
                        }
                    });
            }' : '') . '
            
            // Función para cargar lista de notificaciones de estudiantes en el modal
            ' . ($tieneEstudiantes ? 'function cargarListaNotificacionesEstudiantes() {
                fetch("ajax_notificaciones_estudiantes.php?accion=obtener")
                    .then(response => response.json())
                    .then(data => {
                        const lista = document.getElementById("listaNotificacionesEstudiantes");
                        if (!lista) return;
                        
                        if (data.exito && data.solicitudes && data.solicitudes.length > 0) {
                            let html = "<div class=\"list-group\">";
                            data.solicitudes.forEach(solicitud => {
                                const fecha = new Date(solicitud.fecha_creacion).toLocaleString("es-ES");
                                html += `
                                    <div class="list-group-item" id="solicitud-estudiante-${solicitud.id}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-user-graduate text-primary"></i> 
                                                    ${solicitud.nombres} ${solicitud.apellidos}
                                                </h6>
                                                <p class="mb-1 text-muted small">
                                                    ${solicitud.email ? "<strong>Email:</strong> " + solicitud.email + "<br>" : ""}
                                                    ${solicitud.telefono ? "<strong>Teléfono:</strong> " + solicitud.telefono + "<br>" : ""}
                                                    ${solicitud.direccion ? "<strong>Dirección:</strong> " + solicitud.direccion + "<br>" : ""}
                                                    <small class="text-muted"><i class="fas fa-clock"></i> ${fecha}</small>
                                                </p>
                                            </div>
                                            <div class="btn-group ms-2">
                                                <button class="btn btn-sm btn-success btnAprobarSolicitudEstudiante" 
                                                        data-solicitud-id="${solicitud.id}"
                                                        title="Aprobar solicitud">
                                                    <i class="fas fa-check"></i> Aprobar
                                                </button>
                                                <button class="btn btn-sm btn-danger btnRechazarSolicitudEstudiante" 
                                                        data-solicitud-id="${solicitud.id}"
                                                        title="Rechazar solicitud">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            html += "</div>";
                            lista.innerHTML = html;
                            
                            // Agregar event listeners
                            lista.querySelectorAll(".btnAprobarSolicitudEstudiante").forEach(btn => {
                                btn.addEventListener("click", function() {
                                    const solicitudId = parseInt(this.getAttribute("data-solicitud-id"));
                                    if (window.abrirModalConSolicitudEstudiante) {
                                        window.abrirModalConSolicitudEstudiante(solicitudId);
                                    } else {
                                        window.location.href = "estudiantes.php?notificacion=" + solicitudId;
                                    }
                                });
                            });
                            
                            lista.querySelectorAll(".btnRechazarSolicitudEstudiante").forEach(btn => {
                                btn.addEventListener("click", function() {
                                    rechazarSolicitudEstudiante(parseInt(this.getAttribute("data-solicitud-id")));
                                });
                            });
                        } else {
                            lista.innerHTML = "<p class=\"text-muted text-center mb-0\">No hay solicitudes pendientes</p>";
                        }
                    })
                    .catch(error => {
                        console.error("Error al cargar lista de notificaciones de estudiantes:", error);
                        const lista = document.getElementById("listaNotificacionesEstudiantes");
                        if (lista) {
                            lista.innerHTML = "<p class=\"text-danger text-center\">Error al cargar notificaciones</p>";
                        }
                    });
            }' : '') . '
            
            // Funciones para rechazar solicitudes
            ' . ($tieneDocentes ? 'function rechazarSolicitudDocente(id) {
                if (!confirm("¿Está seguro de rechazar esta solicitud?")) return;
                fetch(`ajax_notificaciones_docentes.php?accion=rechazar&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exito) {
                            const solicitudElement = document.getElementById(`solicitud-docente-${id}`);
                            if (solicitudElement) {
                                solicitudElement.style.transition = "opacity 0.3s";
                                solicitudElement.style.opacity = "0";
                                setTimeout(() => {
                                    solicitudElement.remove();
                                    const lista = document.getElementById("listaNotificacionesDocentes");
                                    if (lista && lista.querySelectorAll(".list-group-item").length === 0) {
                                        lista.innerHTML = "<p class=\"text-muted text-center mb-0\">No hay solicitudes pendientes</p>";
                                    }
                                }, 300);
                            }
                            mostrarNotificacionToast("Solicitud rechazada.", "success");
                            cargarNotificacionesDocentes();
                        } else {
                            mostrarNotificacionToast("Error al rechazar la solicitud: " + (data.mensaje || "Error desconocido"), "danger");
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        mostrarNotificacionToast("Error al rechazar la solicitud", "danger");
                    });
            }' : '') . '
            
            ' . ($tieneMiembros ? 'function rechazarSolicitudMiembro(id) {
                if (!confirm("¿Está seguro de rechazar esta solicitud?")) return;
                fetch(`ajax_notificaciones_miembros.php?accion=rechazar&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exito) {
                            const solicitudElement = document.getElementById(`solicitud-miembro-${id}`);
                            if (solicitudElement) {
                                solicitudElement.style.transition = "opacity 0.3s";
                                solicitudElement.style.opacity = "0";
                                setTimeout(() => {
                                    solicitudElement.remove();
                                    const lista = document.getElementById("listaNotificacionesMiembros");
                                    if (lista && lista.querySelectorAll(".list-group-item").length === 0) {
                                        lista.innerHTML = "<p class=\"text-muted text-center mb-0\">No hay solicitudes pendientes</p>";
                                    }
                                }, 300);
                            }
                            mostrarNotificacionToast("Solicitud rechazada.", "success");
                            cargarNotificacionesMiembros();
                        } else {
                            mostrarNotificacionToast("Error al rechazar la solicitud: " + (data.mensaje || "Error desconocido"), "danger");
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        mostrarNotificacionToast("Error al rechazar la solicitud", "danger");
                    });
            }' : '') . '
            
            ' . ($tieneEstudiantes ? 'function rechazarSolicitudEstudiante(id) {
                if (!confirm("¿Está seguro de rechazar esta solicitud?")) return;
                fetch(`ajax_notificaciones_estudiantes.php?accion=rechazar&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exito) {
                            const solicitudElement = document.getElementById(`solicitud-estudiante-${id}`);
                            if (solicitudElement) {
                                solicitudElement.style.transition = "opacity 0.3s";
                                solicitudElement.style.opacity = "0";
                                setTimeout(() => {
                                    solicitudElement.remove();
                                    const lista = document.getElementById("listaNotificacionesEstudiantes");
                                    if (lista && lista.querySelectorAll(".list-group-item").length === 0) {
                                        lista.innerHTML = "<p class=\"text-muted text-center mb-0\">No hay solicitudes pendientes</p>";
                                    }
                                }, 300);
                            }
                            mostrarNotificacionToast("Solicitud rechazada.", "success");
                            cargarNotificacionesEstudiantes();
                        } else {
                            mostrarNotificacionToast("Error al rechazar la solicitud: " + (data.mensaje || "Error desconocido"), "danger");
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        mostrarNotificacionToast("Error al rechazar la solicitud", "danger");
                    });
            }' : '') . '
            
            // Botón de notificaciones en el navbar
            const btnNotificacionesNavbar = document.getElementById("btnNotificacionesNavbar");
            if (btnNotificacionesNavbar) {
                btnNotificacionesNavbar.addEventListener("click", function() {
                    const modal = new bootstrap.Modal(document.getElementById("modalNotificaciones"));
                    modal.show();
                    ' . ($tieneDocentes ? 'cargarListaNotificacionesDocentes();' : '') . '
                    ' . ($tieneMiembros ? 'cargarListaNotificacionesMiembros();' : '') . '
                    ' . ($tieneEstudiantes ? 'cargarListaNotificacionesEstudiantes();' : '') . '
                });
            }
            
            // Cargar notificaciones al cargar la página y cada 30 segundos
            document.addEventListener("DOMContentLoaded", function() {
                cargarTodasLasNotificaciones();
                setInterval(cargarTodasLasNotificaciones, 30000);
            });
            
            // Cargar notificaciones cuando se abre el modal
            const modalNotificaciones = document.getElementById("modalNotificaciones");
            if (modalNotificaciones) {
                modalNotificaciones.addEventListener("show.bs.modal", function() {
                    ' . ($tieneDocentes ? 'cargarListaNotificacionesDocentes();' : '') . '
                    ' . ($tieneMiembros ? 'cargarListaNotificacionesMiembros();' : '') . '
                    ' . ($tieneEstudiantes ? 'cargarListaNotificacionesEstudiantes();' : '') . '
                });
            }
            
            // Cargar notificaciones cuando se cambia de tab
            ' . ($tieneDocentes ? 'document.getElementById("tab-docentes")?.addEventListener("shown.bs.tab", function() {
                cargarListaNotificacionesDocentes();
            });' : '') . '
            ' . ($tieneMiembros ? 'document.getElementById("tab-miembros")?.addEventListener("shown.bs.tab", function() {
                cargarListaNotificacionesMiembros();
            });' : '') . '
            ' . ($tieneEstudiantes ? 'document.getElementById("tab-estudiantes")?.addEventListener("shown.bs.tab", function() {
                cargarListaNotificacionesEstudiantes();
            });' : '') . '
        })();
    </script>
    ';
}
?>
