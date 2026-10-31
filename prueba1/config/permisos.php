<?php

/*
|--------------------------------------------------------------------------
| Catálogo de permisos
|--------------------------------------------------------------------------
|
| Cada módulo agrupa los permisos que se pueden asignar a un rol desde el
| panel de Roles. El nombre del permiso se usa en las rutas (middleware
| "permiso:"), en el modelo User (tienePermiso) y en las vistas (@permiso).
|
| Convención:
|   - <modulo>.ver       → consultar listados y detalles
|   - <modulo>.gestionar → crear, editar y eliminar
|
| Un permiso ".gestionar" incluye automáticamente el ".ver" del mismo módulo.
|
*/

return [

    'modulos' => [

        'Seguridad' => [
            'icono' => 'bi-shield-lock',
            'permisos' => [
                'usuarios.ver'        => 'Ver listado y detalle de usuarios',
                'usuarios.gestionar'  => 'Crear, editar y eliminar usuarios',
                'roles.ver'           => 'Ver roles y sus permisos',
                'roles.gestionar'     => 'Crear, editar y eliminar roles y sus permisos',
                'permisos.ver'        => 'Ver los permisos de cada rol',
                'permisos.gestionar'  => 'Asignar y quitar permisos a los roles',
                'asignar-roles.ver'   => 'Ver los roles asignados a cada usuario',
                'asignar-roles.gestionar' => 'Asignar y quitar roles a los usuarios',
            ],
        ],

        'Personas' => [
            'icono' => 'bi-people',
            'permisos' => [
                'miembros.ver'       => 'Ver miembros',
                'miembros.gestionar' => 'Crear, editar y eliminar miembros',
                'docentes.ver'       => 'Ver docentes',
                'docentes.gestionar' => 'Crear, editar y eliminar docentes',
                'lideres.ver'        => 'Ver líderes de iglesia',
                'lideres.gestionar'  => 'Crear, editar y eliminar líderes de iglesia',
                'cargos.ver'         => 'Ver cargos',
                'cargos.gestionar'   => 'Crear, editar y eliminar cargos',
            ],
        ],

        'Organización' => [
            'icono' => 'bi-diagram-3',
            'permisos' => [
                'ministerios.ver'       => 'Ver ministerios',
                'ministerios.gestionar' => 'Crear, editar y eliminar ministerios',
                'actividades.ver'       => 'Ver actividades',
                'actividades.gestionar' => 'Crear, editar y eliminar actividades',
                'seguimientos.ver'      => 'Ver seguimientos de actividades',
                'seguimientos.gestionar' => 'Crear, editar y eliminar seguimientos',
            ],
        ],

        'Finanzas' => [
            'icono' => 'bi-cash-coin',
            'permisos' => [
                'cultos.ver'       => 'Ver cultos',
                'cultos.gestionar' => 'Crear, editar y eliminar cultos',
                'ingresos.ver'       => 'Ver ingresos',
                'ingresos.gestionar' => 'Crear, editar y eliminar ingresos',
                'egresos.ver'       => 'Ver egresos',
                'egresos.gestionar' => 'Crear, editar y eliminar egresos',
                'contratos.ver'       => 'Ver contratos',
                'contratos.gestionar' => 'Crear, editar y eliminar contratos',
            ],
        ],

        'Académico' => [
            'icono' => 'bi-book',
            'permisos' => [
                'cursos.ver'       => 'Ver cursos',
                'cursos.gestionar' => 'Crear, editar y eliminar cursos',
                'sesiones.ver'       => 'Ver sesiones de clase',
                'sesiones.gestionar' => 'Crear, editar y eliminar sesiones',
                'inscripciones.ver'       => 'Ver inscripciones',
                'inscripciones.gestionar' => 'Inscribir, editar y retirar alumnos',
                'asistencia.ver'       => 'Ver la asistencia',
                'asistencia.gestionar' => 'Registrar la asistencia de las sesiones',
            ],
        ],

        'Reportes' => [
            'icono' => 'bi-graph-up',
            'permisos' => [
                'reportes.ver' => 'Ver reportes de miembros, asistencia y finanzas',
            ],
        ],

        'Biblia' => [
            'icono' => 'bi-book-half',
            'permisos' => [
                'biblia.ver' => 'Consultar la Biblia digital',
            ],
        ],

    ],

];
