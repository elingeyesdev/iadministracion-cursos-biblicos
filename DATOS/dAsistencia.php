<?php
/**
 * ARCHIVO: dAsistencia.php
 * 
 * Este archivo contiene la clase dAsistencia que maneja todas las operaciones 
 * de acceso a datos relacionadas con el registro de asistencias en el sistema.
 * 
 * Funcionalidades principales:
 * - Registrar asistencias completas de un curso en una fecha específica
 * - Insertar registro de asistencia para todos los estudiantes de un curso
 * - Actualizar registros de asistencia existentes
 * - Eliminar registros de asistencia (soft delete)
 * - Listar asistencias agrupadas por curso, fecha y docente
 * - Buscar asistencias por diferentes criterios (curso, fecha, estudiante, término)
 * - Obtener estadísticas de asistencia por estudiante y por curso
 * - Calcular porcentajes de asistencia, presentes, ausentes y retrasos
 * - Generar reportes detallados de asistencia por curso con estadísticas
 * - Buscar asistencias por estudiante con información completa
 * - Gestionar registros individuales y en lote de asistencias
 * 
 * La clase maneja un sistema unificado de asistencias donde cada registro
 * representa la asistencia de un estudiante específico en un curso, fecha y docente.
 * Permite registrar estados de asistencia: 'presente', 'ausente', 'retraso'.
 * 
 * Utiliza consultas SQL optimizadas con índices y agrupaciones para generar
 * reportes y estadísticas eficientemente.
 */
require_once 'dConexion.php';

class dAsistencia {
    private $conexion;
    
    // Atributos de la entidad Asistencia (ahora unificada)
    private $id;
    private $curso_id;
    private $fecha;
    private $docente_id;
    private $estudiante_id;
    private $estado_asistencia;
    private $observacion;
    private $observaciones;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $curso_id = null, $fecha = null, $docente_id = null, $estudiante_id = null, $estado_asistencia = 'ausente', $observacion = null, $observaciones = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->curso_id = $curso_id ? (int)$curso_id : null;
            $this->fecha = $fecha;
            $this->docente_id = $docente_id ? (int)$docente_id : null;
            $this->estudiante_id = $estudiante_id ? (int)$estudiante_id : null;
            $this->estado_asistencia = $estado_asistencia;
            $this->observacion = $observacion;
            $this->observaciones = $observaciones;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($curso_id, $fecha, $docente_id, $observaciones = '', $detalle_estudiantes = []) {
        if (!$this->conexion) return false;
        
        $curso_id = (int)$curso_id;
        $docente_id = (int)$docente_id;
        $fecha = mysqli_real_escape_string($this->conexion, $fecha);
        $observaciones = mysqli_real_escape_string($this->conexion, $observaciones);
        
        // Crear un array indexado por estudiante_id para acceso rápido
        $detalle_indexado = [];
        if (!empty($detalle_estudiantes) && is_array($detalle_estudiantes)) {
            foreach ($detalle_estudiantes as $detalle) {
                $est_id = (int)($detalle['estudiante_id'] ?? 0);
                if ($est_id > 0) {
                    $detalle_indexado[$est_id] = $detalle;
                }
            }
        }
        
        // Obtener TODOS los estudiantes inscritos en el curso
        $sql_estudiantes_curso = "SELECT e.id as estudiante_id
                                 FROM estudiantes_cursos ec
                                 INNER JOIN estudiantes e ON ec.estudiante_id = e.id
                                 WHERE ec.curso_id = $curso_id AND ec.estado = 1 AND e.estado = 1";
        $resultado_estudiantes = mysqli_query($this->conexion, $sql_estudiantes_curso);
        
        $primer_id = null;
        
        // Insertar registro para TODOS los estudiantes del curso
        if ($resultado_estudiantes) {
            while ($row = mysqli_fetch_assoc($resultado_estudiantes)) {
                $estudiante_id = (int)$row['estudiante_id'];
                
                // Si el estudiante está en el array del formulario, usar su estado
                // Si no está, crear registro con 'ausente' por defecto
                if (isset($detalle_indexado[$estudiante_id])) {
                    $detalle = $detalle_indexado[$estudiante_id];
                    $estado_asistencia = mysqli_real_escape_string($this->conexion, $detalle['estado_asistencia'] ?? 'ausente');
                    $observacion = mysqli_real_escape_string($this->conexion, $detalle['observacion'] ?? '');
                } else {
                    // Si no está en el formulario, crear registro con 'ausente' por defecto
                    $estado_asistencia = 'ausente';
                    $observacion = '';
                }
                
                // Validar estado_asistencia
                if (!in_array($estado_asistencia, ['presente', 'ausente', 'retraso'])) {
                    $estado_asistencia = 'ausente';
                }
                
                // Insertar registro (siempre se guarda, incluso si es ausente)
                $sql = "INSERT INTO asistencias (curso_id, fecha, docente_id, estudiante_id, estado_asistencia, observacion, observaciones, estado) 
                       VALUES ($curso_id, '$fecha', $docente_id, $estudiante_id, '$estado_asistencia', '$observacion', '$observaciones', 1)";
                
                if (mysqli_query($this->conexion, $sql)) {
                    if ($primer_id === null) {
                        $primer_id = mysqli_insert_id($this->conexion);
                    }
                }
            }
            mysqli_free_result($resultado_estudiantes);
        }
        
        return $primer_id;
    }
    
    public function actualizar($id, $curso_id, $fecha, $docente_id, $observaciones = '', $detalle_estudiantes = []) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $curso_id = (int)$curso_id;
        $docente_id = (int)$docente_id;
        $fecha = mysqli_real_escape_string($this->conexion, $fecha);
        $observaciones = mysqli_real_escape_string($this->conexion, $observaciones);
        
        // Obtener curso_id, fecha y docente_id del registro original
        $sql_original = "SELECT curso_id, fecha, docente_id FROM asistencias WHERE id = $id LIMIT 1";
        $resultado_original = mysqli_query($this->conexion, $sql_original);
        $original = null;
        if ($resultado_original && mysqli_num_rows($resultado_original) > 0) {
            $original = mysqli_fetch_assoc($resultado_original);
        }
        if ($resultado_original) {
            mysqli_free_result($resultado_original);
        }
        
        if (!$original) {
            return false;
        }
        
        $curso_original = (int)$original['curso_id'];
        $fecha_original = mysqli_real_escape_string($this->conexion, $original['fecha']);
        $docente_original = (int)$original['docente_id'];
        
        // Eliminar registros existentes para este curso, fecha y docente (eliminación lógica)
        $sql_delete = "UPDATE asistencias SET estado = 0 
                      WHERE curso_id = $curso_original 
                            AND fecha = '$fecha_original' 
                            AND docente_id = $docente_original 
                            AND estado = 1";
        mysqli_query($this->conexion, $sql_delete);
        
        // Crear un array indexado por estudiante_id para acceso rápido
        $detalle_indexado = [];
        if (!empty($detalle_estudiantes) && is_array($detalle_estudiantes)) {
            foreach ($detalle_estudiantes as $detalle) {
                $est_id = (int)($detalle['estudiante_id'] ?? 0);
                if ($est_id > 0) {
                    $detalle_indexado[$est_id] = $detalle;
                }
            }
        }
        
        // Obtener TODOS los estudiantes inscritos en el curso
        $sql_estudiantes_curso = "SELECT e.id as estudiante_id
                                 FROM estudiantes_cursos ec
                                 INNER JOIN estudiantes e ON ec.estudiante_id = e.id
                                 WHERE ec.curso_id = $curso_id AND ec.estado = 1 AND e.estado = 1";
        $resultado_estudiantes = mysqli_query($this->conexion, $sql_estudiantes_curso);
        
        // Insertar registros para TODOS los estudiantes del curso
        if ($resultado_estudiantes) {
            while ($row = mysqli_fetch_assoc($resultado_estudiantes)) {
                $estudiante_id = (int)$row['estudiante_id'];
                
                // Si el estudiante está en el array del formulario, usar su estado
                // Si no está, crear registro con 'ausente' por defecto
                if (isset($detalle_indexado[$estudiante_id])) {
                    $detalle = $detalle_indexado[$estudiante_id];
                    $estado_asistencia = mysqli_real_escape_string($this->conexion, $detalle['estado_asistencia'] ?? 'ausente');
                    $observacion = mysqli_real_escape_string($this->conexion, $detalle['observacion'] ?? '');
                } else {
                    // Si no está en el formulario, crear registro con 'ausente' por defecto
                    $estado_asistencia = 'ausente';
                    $observacion = '';
                }
                
                // Validar estado_asistencia
                if (!in_array($estado_asistencia, ['presente', 'ausente', 'retraso'])) {
                    $estado_asistencia = 'ausente';
                }
                
                // Insertar registro (siempre se guarda, incluso si es ausente)
                $sql = "INSERT INTO asistencias (curso_id, fecha, docente_id, estudiante_id, estado_asistencia, observacion, observaciones, estado) 
                       VALUES ($curso_id, '$fecha', $docente_id, $estudiante_id, '$estado_asistencia', '$observacion', '$observaciones', 1)";
                mysqli_query($this->conexion, $sql);
            }
            mysqli_free_result($resultado_estudiantes);
        }
        
        return true;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        
        // Obtener curso_id, fecha y docente_id del registro
        $sql_info = "SELECT curso_id, fecha, docente_id FROM asistencias WHERE id = $id LIMIT 1";
        $resultado_info = mysqli_query($this->conexion, $sql_info);
        $info = null;
        if ($resultado_info && mysqli_num_rows($resultado_info) > 0) {
            $info = mysqli_fetch_assoc($resultado_info);
        }
        if ($resultado_info) {
            mysqli_free_result($resultado_info);
        }
        
        if (!$info) {
            return false;
        }
        
        $curso_id = (int)$info['curso_id'];
        $fecha = mysqli_real_escape_string($this->conexion, $info['fecha']);
        $docente_id = (int)$info['docente_id'];
        
        // Eliminar todos los registros de esta asistencia (mismo curso, fecha y docente)
        $sql = "UPDATE asistencias SET estado = 0 
                WHERE curso_id = $curso_id 
                      AND fecha = '$fecha' 
                      AND docente_id = $docente_id 
                      AND estado = 1";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        // Agrupar por curso y fecha para mostrar resumen
        $sql = "SELECT 
                    a.curso_id, 
                    a.fecha, 
                    a.docente_id,
                    c.nombre as nombre_curso,
                    d.nombres as docente_nombre, 
                    d.apellidos as docente_apellido,
                    COUNT(DISTINCT a.estudiante_id) as total_estudiantes,
                    SUM(CASE WHEN a.estado_asistencia = 'presente' THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN a.estado_asistencia = 'retraso' THEN 1 ELSE 0 END) as retrasos,
                    MIN(a.id) as id
                FROM asistencias a 
                LEFT JOIN cursos c ON a.curso_id = c.id
                LEFT JOIN docentes d ON a.docente_id = d.id
                WHERE a.estado = 1
                GROUP BY a.curso_id, a.fecha, a.docente_id
                ORDER BY a.fecha DESC, c.nombre";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $asistencias = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $asistencias[] = $row;
            }
            mysqli_free_result($resultado);
        }
        
        return $asistencias;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        // Obtener el primer registro para obtener curso, fecha, docente
        $sql = "SELECT DISTINCT
                    a.curso_id, 
                    a.fecha, 
                    a.docente_id,
                    a.observaciones,
                    c.nombre as nombre_curso,
                    d.nombres as docente_nombre, 
                    d.apellidos as docente_apellido
                FROM asistencias a 
                LEFT JOIN cursos c ON a.curso_id = c.id
                LEFT JOIN docentes d ON a.docente_id = d.id
                WHERE a.id = $id AND a.estado = 1
                LIMIT 1";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $asistencia = null;
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $asistencia = mysqli_fetch_assoc($resultado);
            
            // Obtener todos los estudiantes de esta asistencia (mismo curso y fecha)
            $curso_id = (int)$asistencia['curso_id'];
            $fecha = mysqli_real_escape_string($this->conexion, $asistencia['fecha']);
            $docente_id = (int)$asistencia['docente_id'];
            
            $sql_detalle = "SELECT a.id, a.estudiante_id, 
                                  a.estado_asistencia,
                                  a.observacion,
                                  e.nombres as estudiante_nombre, 
                                  e.apellidos as estudiante_apellido
                           FROM asistencias a
                           INNER JOIN estudiantes e ON a.estudiante_id = e.id
                           WHERE a.curso_id = $curso_id 
                                 AND a.fecha = '$fecha' 
                                 AND a.docente_id = $docente_id
                                 AND a.estado = 1
                           ORDER BY e.apellidos, e.nombres";
            $resultado_detalle = mysqli_query($this->conexion, $sql_detalle);
            $asistencia['detalle'] = [];
            if ($resultado_detalle) {
                while ($row = mysqli_fetch_assoc($resultado_detalle)) {
                    $asistencia['detalle'][] = $row;
                }
                mysqli_free_result($resultado_detalle);
            }
        }
        
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        return $asistencia;
    }
    
    public function buscarPorCursoYFecha($curso_id, $fecha) {
        if (!$this->conexion) return [];
        
        $curso_id = (int)$curso_id;
        $fecha = mysqli_real_escape_string($this->conexion, $fecha);
        
        $sql = "SELECT a.id, a.estudiante_id, 
                       a.estado_asistencia,
                       a.observacion,
                       e.nombres as estudiante_nombre, 
                       e.apellidos as estudiante_apellido,
                       a.fecha, 
                       c.nombre as nombre_curso,
                       a.docente_id
                FROM asistencias a 
                INNER JOIN estudiantes e ON a.estudiante_id = e.id
                INNER JOIN cursos c ON a.curso_id = c.id
                WHERE a.curso_id = $curso_id AND a.fecha = '$fecha' AND a.estado = 1
                ORDER BY e.apellidos, e.nombres";
        
        $resultado = mysqli_query($this->conexion, $sql);
        
        $asistencias = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $asistencias[] = $row;
            }
            mysqli_free_result($resultado);
        }
        
        return $asistencias;
    }
    
    // Obtener asistencia_id por curso y fecha (retorna el primer ID encontrado)
    public function obtenerAsistenciaIdPorCursoYFecha($curso_id, $fecha) {
        if (!$this->conexion) return null;
        
        $curso_id = (int)$curso_id;
        $fecha = mysqli_real_escape_string($this->conexion, $fecha);
        
        $sql = "SELECT id FROM asistencias 
                WHERE curso_id = $curso_id AND fecha = '$fecha' AND estado = 1 
                LIMIT 1";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $asistencia_id = null;
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $row = mysqli_fetch_assoc($resultado);
            $asistencia_id = (int)$row['id'];
        }
        
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        
        return $asistencia_id;
    }
    
    public function buscarPorMiembroYCurso($miembro_id, $curso_id) {
        if (!$this->conexion) return [];
        
        $miembro_id = (int)$miembro_id;
        $curso_id = (int)$curso_id;
        
        $sql = "SELECT a.id, a.estudiante_id, 
                       a.estado_asistencia,
                       a.observacion,
                       a.fecha, 
                       c.nombre as nombre_curso
                FROM asistencias a 
                INNER JOIN estudiantes e ON a.estudiante_id = e.id
                INNER JOIN cursos c ON a.curso_id = c.id
                WHERE e.miembro_id = $miembro_id AND a.curso_id = $curso_id AND a.estado = 1
                ORDER BY a.fecha DESC";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $asistencias = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $asistencias[] = $row;
            }
            mysqli_free_result($resultado);
        }
        
        return $asistencias;
    }
    
    // Eliminar solo un registro individual
    public function eliminarDetalle($detalle_id) {
        if (!$this->conexion) return false;
        
        $detalle_id = (int)$detalle_id;
        // Eliminación lógica usando la columna estado
        $sql = "UPDATE asistencias SET estado = 0 WHERE id = $detalle_id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    // Insertar registro individual de asistencia
    public function insertarIndividual($asistencia_id, $estudiante_id, $estado_asistencia = 'ausente', $observacion = '') {
        if (!$this->conexion) return false;
        
        // Obtener curso_id, fecha y docente_id del registro de referencia
        $asistencia_id = (int)$asistencia_id;
        $sql_info = "SELECT curso_id, fecha, docente_id FROM asistencias WHERE id = $asistencia_id LIMIT 1";
        $resultado_info = mysqli_query($this->conexion, $sql_info);
        $info = null;
        if ($resultado_info && mysqli_num_rows($resultado_info) > 0) {
            $info = mysqli_fetch_assoc($resultado_info);
        }
        if ($resultado_info) {
            mysqli_free_result($resultado_info);
        }
        
        if (!$info) {
            return false;
        }
        
        $curso_id = (int)$info['curso_id'];
        $fecha = mysqli_real_escape_string($this->conexion, $info['fecha']);
        $docente_id = (int)$info['docente_id'];
        $estudiante_id = (int)$estudiante_id;
        $estado_asistencia = mysqli_real_escape_string($this->conexion, $estado_asistencia);
        if (!in_array($estado_asistencia, ['presente', 'ausente', 'retraso'])) {
            $estado_asistencia = 'ausente';
        }
        $observacion = mysqli_real_escape_string($this->conexion, $observacion);
        
        // Verificar si ya existe un registro para este estudiante en esta asistencia
        $sql_check = "SELECT id FROM asistencias 
                      WHERE curso_id = $curso_id 
                            AND fecha = '$fecha' 
                            AND docente_id = $docente_id 
                            AND estudiante_id = $estudiante_id 
                            AND estado = 1
                      LIMIT 1";
        $result_check = mysqli_query($this->conexion, $sql_check);
        if ($result_check && mysqli_num_rows($result_check) > 0) {
            // Actualizar existente
            $row = mysqli_fetch_assoc($result_check);
            mysqli_free_result($result_check);
            $sql = "UPDATE asistencias 
                    SET estado_asistencia = '$estado_asistencia', observacion = '$observacion' 
                    WHERE id = " . (int)$row['id'];
        } else {
            // Insertar nuevo
            $sql = "INSERT INTO asistencias (curso_id, fecha, docente_id, estudiante_id, estado_asistencia, observacion, estado) 
                    VALUES ($curso_id, '$fecha', $docente_id, $estudiante_id, '$estado_asistencia', '$observacion', 1)";
        }
        
        $resultado = mysqli_query($this->conexion, $sql);
        $id = $resultado ? mysqli_insert_id($this->conexion) : false;
        
        return $id;
    }
    
    // Buscar registros de asistencia
    public function buscar($termino, $curso_id = null, $fecha_desde = null, $fecha_hasta = null) {
        if (!$this->conexion) return [];
        
        $where = [];
        
        // Manejar término de búsqueda - solo agregar si tiene valor
        if ($termino !== null && $termino !== '' && is_string($termino)) {
            $termino_trim = trim($termino);
            if ($termino_trim !== '') {
                $termino = mysqli_real_escape_string($this->conexion, $termino_trim);
                $where[] = "(e.nombres LIKE '%$termino%' OR e.apellidos LIKE '%$termino%' OR c.nombre LIKE '%$termino%')";
            }
        }
        
        // Manejar curso_id - siempre debe estar presente
        if ($curso_id !== null && $curso_id !== '') {
            $curso_id = (int)$curso_id;
            $where[] = "a.curso_id = $curso_id";
        }
        
        // Manejar fecha_desde - solo procesar si es un string no vacío
        if ($fecha_desde !== null && $fecha_desde !== '') {
            $fecha_desde_trim = trim((string)$fecha_desde);
            if ($fecha_desde_trim !== '') {
                $fecha_desde = mysqli_real_escape_string($this->conexion, $fecha_desde_trim);
                $where[] = "a.fecha >= '$fecha_desde'";
            }
        }
        
        // Manejar fecha_hasta - solo procesar si es un string no vacío
        if ($fecha_hasta !== null && $fecha_hasta !== '') {
            $fecha_hasta_trim = trim((string)$fecha_hasta);
            if ($fecha_hasta_trim !== '') {
                $fecha_hasta = mysqli_real_escape_string($this->conexion, $fecha_hasta_trim);
                $where[] = "a.fecha <= '$fecha_hasta'";
            }
        }
        
        // Construir la cláusula WHERE
        if (!empty($where)) {
            $where_clause = "WHERE " . implode(" AND ", $where) . " AND a.estado = 1";
        } else {
            $where_clause = "WHERE a.estado = 1";
        }
        
        $sql = "SELECT a.id, a.estudiante_id, 
                       a.estado_asistencia,
                       a.observacion,
                       e.nombres as estudiante_nombre, 
                       e.apellidos as estudiante_apellido,
                       a.fecha, 
                       c.nombre as nombre_curso, 
                       c.id as curso_id,
                       d.nombres as docente_nombre, 
                       d.apellidos as docente_apellido
                FROM asistencias a
                INNER JOIN estudiantes e ON a.estudiante_id = e.id
                INNER JOIN cursos c ON a.curso_id = c.id
                LEFT JOIN docentes d ON a.docente_id = d.id
                $where_clause
                ORDER BY a.fecha DESC, e.apellidos, e.nombres
                LIMIT 100";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $asistencias = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $asistencias[] = $row;
            }
            mysqli_free_result($resultado);
        } else {
            // Log del error para debugging
            error_log("Error en buscar asistencias: " . mysqli_error($this->conexion));
            error_log("SQL ejecutado: " . $sql);
        }
        
        return $asistencias;
    }
    
    // Obtener estadísticas por estudiante
    public function obtenerEstadisticasPorEstudiante($estudiante_id, $curso_id = null) {
        if (!$this->conexion) return [];
        
        $estudiante_id = (int)$estudiante_id;
        $where = "a.estudiante_id = $estudiante_id AND a.estado = 1";
        
        if ($curso_id) {
            $curso_id = (int)$curso_id;
            $where .= " AND a.curso_id = $curso_id";
        }
        
        $sql = "SELECT 
                COUNT(DISTINCT CONCAT(a.curso_id, '-', a.fecha, '-', a.docente_id)) as total_clases,
                COUNT(a.id) as total_registros,
                SUM(CASE WHEN a.estado_asistencia = 'presente' THEN 1 ELSE 0 END) as total_presentes,
                SUM(CASE WHEN a.estado_asistencia = 'ausente' THEN 1 ELSE 0 END) as total_ausentes,
                SUM(CASE WHEN a.estado_asistencia = 'retraso' THEN 1 ELSE 0 END) as total_retrasos,
                CASE 
                    WHEN COUNT(a.id) > 0 THEN 
                        ROUND(SUM(CASE WHEN a.estado_asistencia = 'presente' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id), 2)
                    ELSE 0
                END as porcentaje_asistencia
                FROM asistencias a
                WHERE $where";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $estadisticas = [];
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $estadisticas = mysqli_fetch_assoc($resultado);
        }
        
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        return $estadisticas;
    }
    
    // Obtener estadísticas por estudiante y curso (detallado)
    public function obtenerEstadisticasDetalladasPorEstudiante($estudiante_id, $curso_id) {
        if (!$this->conexion) return [];
        
        $estudiante_id = (int)$estudiante_id;
        $curso_id = (int)$curso_id;
        
        $sql = "SELECT 
                a.fecha,
                a.estado_asistencia,
                a.observacion,
                c.nombre as curso_nombre
                FROM asistencias a
                INNER JOIN cursos c ON a.curso_id = c.id
                WHERE a.estudiante_id = $estudiante_id AND a.curso_id = $curso_id AND a.estado = 1
                ORDER BY a.fecha DESC";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $registros = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $registros[] = $row;
            }
            mysqli_free_result($resultado);
        }
        
        return $registros;
    }
    
    public function obtenerEstadisticasPorCurso($curso_id) {
        if (!$this->conexion) return [];
        
        $curso_id = (int)$curso_id;
        
        $sql = "SELECT 
                COUNT(DISTINCT CONCAT(a.curso_id, '-', a.fecha, '-', a.docente_id)) as total_clases,
                COUNT(a.id) as total_registros,
                SUM(CASE WHEN a.estado_asistencia = 'presente' THEN 1 ELSE 0 END) as total_presentes,
                SUM(CASE WHEN a.estado_asistencia = 'ausente' THEN 1 ELSE 0 END) as total_ausentes
                FROM asistencias a
                WHERE a.curso_id = $curso_id AND a.estado = 1";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $estadisticas = [];
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $estadisticas = mysqli_fetch_assoc($resultado);
        }
        
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        return $estadisticas;
    }
    
    // Buscar asistencias por estudiante con información de quién lo registró
    public function buscarAsistenciasPorEstudiante($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        
        // Primero obtener estudiantes que coincidan
        $sql_estudiantes = "SELECT DISTINCT e.id, e.nombres, e.apellidos, e.email, e.telefono
                           FROM estudiantes e
                           WHERE (e.nombres LIKE '$like' OR e.apellidos LIKE '$like' OR 
                                  CONCAT(e.nombres, ' ', e.apellidos) LIKE '$like')
                           AND e.estado = 1
                           ORDER BY e.apellidos, e.nombres
                           LIMIT 50";
        
        $resultado_estudiantes = mysqli_query($this->conexion, $sql_estudiantes);
        $estudiantes = [];
        
        if ($resultado_estudiantes) {
            while ($estudiante = mysqli_fetch_assoc($resultado_estudiantes)) {
                $estudiante_id = (int)$estudiante['id'];
                
                // Obtener registros de asistencia del estudiante
                $sql_asistencias = "SELECT a.id as asistencia_id, a.fecha, a.observaciones,
                                           a.estado_asistencia,
                                           a.observacion as detalle_observacion,
                                           c.nombre as curso_nombre, 
                                           c.id as curso_id,
                                           d.nombres as docente_nombre, 
                                           d.apellidos as docente_apellido,
                                           d.id as docente_id
                                    FROM asistencias a
                                    INNER JOIN cursos c ON a.curso_id = c.id
                                    LEFT JOIN docentes d ON a.docente_id = d.id
                                    WHERE a.estudiante_id = $estudiante_id AND a.estado = 1
                                    ORDER BY a.fecha DESC";
                
                $resultado_asistencias = mysqli_query($this->conexion, $sql_asistencias);
                $estudiante['asistencias'] = [];
                
                if ($resultado_asistencias) {
                    while ($asistencia = mysqli_fetch_assoc($resultado_asistencias)) {
                        $estudiante['asistencias'][] = $asistencia;
                    }
                    mysqli_free_result($resultado_asistencias);
                }
                
                $estudiantes[] = $estudiante;
            }
            mysqli_free_result($resultado_estudiantes);
        }
        
        return $estudiantes;
    }
    
    // Obtener reporte detallado de asistencias por curso con estadísticas de estudiantes
    public function obtenerReporteDetalladoPorCurso($curso_id_filtro = null) {
        if (!$this->conexion) return [];
        
        $where_curso = '';
        if ($curso_id_filtro) {
            $curso_id_filtro = (int)$curso_id_filtro;
            $where_curso = "AND c.id = $curso_id_filtro";
        }
        
        $sql = "SELECT DISTINCT
                    c.id as curso_id,
                    c.nombre as curso_nombre,
                    c.descripcion as curso_descripcion,
                    d.id as docente_id,
                    d.nombres as docente_nombre,
                    d.apellidos as docente_apellido
                FROM cursos c
                LEFT JOIN docentes d ON c.docente_id = d.id
                WHERE c.estado = 1 $where_curso
                ORDER BY c.nombre";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $cursos = [];
        
        if ($resultado) {
            while ($curso = mysqli_fetch_assoc($resultado)) {
                $curso_id = (int)$curso['curso_id'];
                
                // Obtener estudiantes del curso con sus estadísticas
                $sql_estudiantes = "SELECT DISTINCT e.id as estudiante_id, e.nombres, e.apellidos
                FROM estudiantes_cursos ec
                INNER JOIN estudiantes e ON ec.estudiante_id = e.id
                WHERE ec.curso_id = $curso_id AND ec.estado = 1 AND e.estado = 1
                ORDER BY e.apellidos, e.nombres";
                
                $resultado_estudiantes = mysqli_query($this->conexion, $sql_estudiantes);
                
                if ($resultado_estudiantes) {
                    $curso['estudiantes'] = [];
                    while ($estudiante = mysqli_fetch_assoc($resultado_estudiantes)) {
                        $estudiante_id = (int)$estudiante['estudiante_id'];
                        
                        // Obtener estadísticas del estudiante en este curso
                        $sql_stats = "SELECT 
                            COUNT(DISTINCT CONCAT(a.curso_id, '-', a.fecha, '-', a.docente_id)) as total_clases,
                            COUNT(a.id) as total_registros,
                            COALESCE(SUM(CASE WHEN a.estado_asistencia = 'presente' THEN 1 ELSE 0 END), 0) as total_presentes,
                            COALESCE(SUM(CASE WHEN a.estado_asistencia = 'ausente' THEN 1 ELSE 0 END), 0) as total_ausentes,
                            COALESCE(SUM(CASE WHEN a.estado_asistencia = 'retraso' THEN 1 ELSE 0 END), 0) as total_retrasos,
                            CASE 
                                WHEN COUNT(a.id) > 0 THEN 
                                    ROUND(SUM(CASE WHEN a.estado_asistencia = 'presente' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id), 2)
                                ELSE 0
                            END as porcentaje_asistencia
                        FROM asistencias a
                        WHERE a.estudiante_id = $estudiante_id AND a.curso_id = $curso_id AND a.estado = 1";
                        
                        $resultado_stats = mysqli_query($this->conexion, $sql_stats);
                        // Inicializar valores por defecto
                        $estudiante['total_clases'] = 0;
                        $estudiante['total_registros'] = 0;
                        $estudiante['asistencias'] = 0;
                        $estudiante['ausencias'] = 0;
                        $estudiante['retrasos'] = 0;
                        $estudiante['porcentaje_asistencia'] = 0;
                        
                        if ($resultado_stats) {
                            $stats = mysqli_fetch_assoc($resultado_stats);
                            if ($stats) {
                                // Mapear a los nombres que espera el reporte
                                $estudiante['total_clases'] = (int)($stats['total_clases'] ?? 0);
                                $estudiante['total_registros'] = (int)($stats['total_registros'] ?? 0);
                                $estudiante['asistencias'] = (int)($stats['total_presentes'] ?? 0);
                                $estudiante['ausencias'] = (int)($stats['total_ausentes'] ?? 0);
                                $estudiante['retrasos'] = (int)($stats['total_retrasos'] ?? 0);
                                $estudiante['porcentaje_asistencia'] = (float)($stats['porcentaje_asistencia'] ?? 0);
                            }
                            mysqli_free_result($resultado_stats);
                        }
                        
                        $curso['estudiantes'][] = $estudiante;
                    }
                    mysqli_free_result($resultado_estudiantes);
                } else {
                    $curso['estudiantes'] = [];
                }
                
                $cursos[] = $curso;
            }
            mysqli_free_result($resultado);
        }
        
        return $cursos;
    }
}
