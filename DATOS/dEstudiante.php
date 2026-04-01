<?php
/**
 * ARCHIVO: dEstudiante.php
 * 
 * Este archivo contiene la clase dEstudiante que maneja todas las operaciones 
 * de acceso a datos relacionadas con estudiantes en el sistema.
 * 
 * Funcionalidades principales:
 * - Crear nuevos estudiantes desde cero
 * - Actualizar información de estudiantes existentes
 * - Eliminar estudiantes (soft delete)
 * - Listar todos los estudiantes con sus cursos inscritos
 * - Inscribir estudiantes en cursos
 * - Buscar estudiantes por nombre o término de búsqueda
 * - Obtener datos de miembros para copiar a estudiantes
 * - Contar estudiantes activos y por curso
 * - Gestionar la relación entre estudiantes y cursos
 * 
 * La clase utiliza procedimientos almacenados y consultas SQL directas para 
 * interactuar con la base de datos MySQL a través de mysqli.
 */
require_once 'dConexion.php';

class dEstudiante {
    private $conexion;
    
    // Atributos de la entidad Estudiante
    private $id;
    private $miembro_id;
    private $nombres;
    private $apellidos;
    private $fecha_nacimiento;
    private $telefono;
    private $email;
    private $direccion;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombres = null, $apellidos = null, $fecha_nacimiento = null, $telefono = null, $email = null, $direccion = null, $miembro_id = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->nombres = $nombres;
            $this->apellidos = $apellidos;
            $this->fecha_nacimiento = $fecha_nacimiento;
            $this->telefono = $telefono;
            $this->email = $email;
            $this->direccion = $direccion;
            $this->miembro_id = $miembro_id ? (int)$miembro_id : null;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($miembro_id, $curso_id) {
        if (!$this->conexion) return false;
        
        $miembro_id = (int)$miembro_id;
        $curso_id = (int)$curso_id;
        
        // Buscar estudiante por miembro
        $sqlEst = "CALL sp_estudiante_buscar_por_miembro($miembro_id)";
        $resultadoEst = mysqli_query($this->conexion, $sqlEst);
        $estudiante = $resultadoEst ? mysqli_fetch_assoc($resultadoEst) : null;
        if ($resultadoEst) {
            mysqli_free_result($resultadoEst);
            mysqli_next_result($this->conexion);
        }
        
        if (!$estudiante || !isset($estudiante['id'])) {
            // No se puede inscribir si no existe el estudiante asociado al miembro
            return false;
        }
        
        $estudiante_id = (int)$estudiante['id'];
        
        // Inscribir al curso
        $sqlIns = "CALL sp_estudiante_curso_inscribir($estudiante_id, $curso_id)";
        $resultadoIns = mysqli_query($this->conexion, $sqlIns);
        if ($resultadoIns) {
            $row = mysqli_fetch_assoc($resultadoIns);
            mysqli_free_result($resultadoIns);
            mysqli_next_result($this->conexion);
            return $row && isset($row['id']) ? (int)$row['id'] : true;
        }
        
        return false;
    }
    
    public function actualizar($id, $miembro_id, $curso_id, $estado) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $miembro_id = (int)$miembro_id;
        $curso_id = (int)$curso_id;
        $estadoBool = ($estado === '1' || $estado === 1 || $estado === 'activo') ? 1 : 0;
        
        // Obtener estudiante_id desde miembro_id
        $sqlEst = "CALL sp_estudiante_buscar_por_miembro($miembro_id)";
        $resultadoEst = mysqli_query($this->conexion, $sqlEst);
        $estudiante = $resultadoEst ? mysqli_fetch_assoc($resultadoEst) : null;
        if ($resultadoEst) {
            mysqli_free_result($resultadoEst);
            mysqli_next_result($this->conexion);
        }
        if (!$estudiante || !isset($estudiante['id'])) {
            return false;
        }
        $estudiante_id = (int)$estudiante['id'];
        
        $sql = "UPDATE estudiantes_cursos 
                SET estudiante_id = $estudiante_id, curso_id = $curso_id, estado = $estadoBool
                WHERE id = $id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return (bool)$resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        
        $sql = "UPDATE estudiantes_cursos SET estado = 0 WHERE id = $id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return (bool)$resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                    e.id as estudiante_id,
                    e.nombres as estudiante_nombre,
                    e.apellidos as estudiante_apellido,
                    e.email,
                    e.telefono,
                    e.direccion,
                    e.miembro_id,
                    e.estado as estudiante_estado,
                    GROUP_CONCAT(DISTINCT c.nombre ORDER BY c.nombre SEPARATOR ', ') as cursos_inscritos,
                    COUNT(DISTINCT ec.curso_id) as total_cursos
                FROM estudiantes e
                LEFT JOIN estudiantes_cursos ec ON e.id = ec.estudiante_id AND ec.estado = 1
                LEFT JOIN cursos c ON ec.curso_id = c.id AND c.estado = 1
                WHERE e.estado = 1
                GROUP BY e.id, e.nombres, e.apellidos, e.email, e.telefono, e.direccion, e.miembro_id, e.estado
                ORDER BY e.apellidos, e.nombres";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $estudiantes = [];
        
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $estudiantes[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $estudiantes;
    }
    
    // Buscar estudiantes por nombre o término
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        
        $sql = "SELECT 
                    e.id as estudiante_id,
                    e.nombres as estudiante_nombre,
                    e.apellidos as estudiante_apellido,
                    e.email,
                    e.telefono,
                    e.direccion,
                    e.miembro_id,
                    e.estado as estudiante_estado,
                    GROUP_CONCAT(DISTINCT c.nombre ORDER BY c.nombre SEPARATOR ', ') as cursos_inscritos,
                    COUNT(DISTINCT ec.curso_id) as total_cursos
                FROM estudiantes e
                LEFT JOIN estudiantes_cursos ec ON e.id = ec.estudiante_id AND ec.estado = 1
                LEFT JOIN cursos c ON ec.curso_id = c.id AND c.estado = 1
                WHERE e.estado = 1 
                AND (e.nombres LIKE '$like' OR e.apellidos LIKE '$like' OR CONCAT(e.nombres, ' ', e.apellidos) LIKE '$like' OR e.id = '$termino')
                GROUP BY e.id, e.nombres, e.apellidos, e.email, e.telefono, e.direccion, e.miembro_id, e.estado
                ORDER BY e.apellidos, e.nombres";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $estudiantes = [];
        
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $estudiantes[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $estudiantes;
    }
    
    public function contarEstudiantesActivos() {
        if (!$this->conexion) return 0;
        
        $sql = "SELECT COUNT(*) AS total FROM estudiantes WHERE estado = 1";
        $resultado = mysqli_query($this->conexion, $sql);
        $row = $resultado ? mysqli_fetch_assoc($resultado) : ['total' => 0];
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        return (int)($row['total'] ?? 0);
    }
    
    public function contarEstudiantesPorCurso($curso_id) {
        if (!$this->conexion) return 0;
        
        $curso_id = (int)$curso_id;
        
        $sql = "SELECT COUNT(*) AS total FROM estudiantes_cursos WHERE curso_id = $curso_id AND estado = 1";
        $resultado = mysqli_query($this->conexion, $sql);
        $row = $resultado ? mysqli_fetch_assoc($resultado) : ['total' => 0];
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        return (int)($row['total'] ?? 0);
    }
    
    // Crear estudiante desde cero
    public function crearEstudiante($nombres, $apellidos, $fecha_nacimiento = null, $telefono = '', $email = '', $direccion = '', $miembro_id = null, $estado = 1) {
        if (!$this->conexion) return false;
        
        $nombres = mysqli_real_escape_string($this->conexion, $nombres);
        $apellidos = mysqli_real_escape_string($this->conexion, $apellidos);
        $telefono = mysqli_real_escape_string($this->conexion, $telefono);
        $email = mysqli_real_escape_string($this->conexion, $email);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        $fecha_nacimiento = $fecha_nacimiento ? "'" . mysqli_real_escape_string($this->conexion, $fecha_nacimiento) . "'" : 'NULL';
        $miembro_id = $miembro_id ? (int)$miembro_id : 'NULL';
        $estado = (int)$estado;
        
        $sql = "INSERT INTO estudiantes (nombres, apellidos, fecha_nacimiento, telefono, email, direccion, miembro_id, estado) 
                VALUES ('$nombres', '$apellidos', $fecha_nacimiento, '$telefono', '$email', '$direccion', $miembro_id, $estado)";
        
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            $id = mysqli_insert_id($this->conexion);
            return $id;
        }
        
        return false;
    }
    
    // Obtener estudiante por ID (solo datos del estudiante, no inscripción)
    public function obtenerEstudiantePorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        
        $sql = "SELECT e.*, m.nombres as miembro_nombre, m.apellidos as miembro_apellido
                FROM estudiantes e
                LEFT JOIN miembros m ON e.miembro_id = m.id
                WHERE e.id = $id";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $estudiante = $resultado ? mysqli_fetch_assoc($resultado) : null;
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        return $estudiante;
    }
    
    // Actualizar datos del estudiante
    public function actualizarEstudiante($id, $nombres, $apellidos, $fecha_nacimiento = null, $telefono = '', $email = '', $direccion = '', $miembro_id = null, $estado = 1) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $nombres = mysqli_real_escape_string($this->conexion, $nombres);
        $apellidos = mysqli_real_escape_string($this->conexion, $apellidos);
        $telefono = mysqli_real_escape_string($this->conexion, $telefono);
        $email = mysqli_real_escape_string($this->conexion, $email);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        $fecha_nacimiento = $fecha_nacimiento ? "'" . mysqli_real_escape_string($this->conexion, $fecha_nacimiento) . "'" : 'NULL';
        $miembro_id = $miembro_id ? (int)$miembro_id : 'NULL';
        $estado = (int)$estado;
        
        $sql = "UPDATE estudiantes 
                SET nombres = '$nombres', apellidos = '$apellidos', fecha_nacimiento = $fecha_nacimiento, 
                    telefono = '$telefono', email = '$email', direccion = '$direccion', 
                    miembro_id = $miembro_id, estado = $estado
                WHERE id = $id";
        
        $resultado = mysqli_query($this->conexion, $sql);
        return (bool)$resultado;
    }
    
    // Inscribir estudiante en curso
    public function inscribirEnCurso($estudiante_id, $curso_id, $fecha_inscripcion = null) {
        if (!$this->conexion) {
            error_log("Error: No se pudo conectar a la base de datos");
            return false;
        }
        
        $estudiante_id = (int)$estudiante_id;
        $curso_id = (int)$curso_id;
        
        // Validar que el estudiante y curso existan
        $sql_val_est = "SELECT id FROM estudiantes WHERE id = $estudiante_id AND estado = 1";
        $result_val_est = mysqli_query($this->conexion, $sql_val_est);
        if (!$result_val_est || mysqli_num_rows($result_val_est) == 0) {
            error_log("Error: Estudiante con ID $estudiante_id no existe o está inactivo");
            if ($result_val_est) mysqli_free_result($result_val_est);
            return false;
        }
        mysqli_free_result($result_val_est);
        
        $sql_val_curso = "SELECT id FROM cursos WHERE id = $curso_id AND estado = 1";
        $result_val_curso = mysqli_query($this->conexion, $sql_val_curso);
        if (!$result_val_curso || mysqli_num_rows($result_val_curso) == 0) {
            error_log("Error: Curso con ID $curso_id no existe o está inactivo");
            if ($result_val_curso) mysqli_free_result($result_val_curso);
            return false;
        }
        mysqli_free_result($result_val_curso);
        
        // Formatear fecha de inscripción
        if ($fecha_inscripcion) {
            $fecha_inscripcion = "'" . mysqli_real_escape_string($this->conexion, $fecha_inscripcion) . "'";
        } else {
            $fecha_inscripcion = 'CURRENT_DATE';
        }
        
        // Verificar si ya está inscrito (incluyendo inactivos)
        $sql_check = "SELECT id, estado FROM estudiantes_cursos WHERE estudiante_id = $estudiante_id AND curso_id = $curso_id";
        $resultado_check = mysqli_query($this->conexion, $sql_check);
        if ($resultado_check && mysqli_num_rows($resultado_check) > 0) {
            $row = mysqli_fetch_assoc($resultado_check);
            mysqli_free_result($resultado_check);
            
            // Si ya existe pero está inactivo, reactivarlo
            if ($row['estado'] == 0) {
                $sql_update = "UPDATE estudiantes_cursos SET estado = 1, fecha_inscripcion = $fecha_inscripcion WHERE id = " . (int)$row['id'];
                $resultado_update = mysqli_query($this->conexion, $sql_update);
                if (!$resultado_update) {
                    error_log("Error al actualizar inscripción: " . mysqli_error($this->conexion));
                    return false;
                }
                return (int)$row['id'];
            }
            
            error_log("Estudiante $estudiante_id ya está inscrito en curso $curso_id");
            return false; // Ya está inscrito y activo
        }
        if ($resultado_check) {
            mysqli_free_result($resultado_check);
        }
        
        // Insertar nueva inscripción con estado explícito
        $sql = "INSERT INTO estudiantes_cursos (estudiante_id, curso_id, fecha_inscripcion, estado) 
                VALUES ($estudiante_id, $curso_id, $fecha_inscripcion, 1)";
        
        error_log("Ejecutando SQL: $sql");
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            $id = mysqli_insert_id($this->conexion);
            error_log("Inscripción creada exitosamente con ID: $id");
            return $id;
        } else {
            $error = mysqli_error($this->conexion);
            error_log("Error al inscribir estudiante: $error");
            error_log("SQL ejecutado: $sql");
            return false;
        }
    }
    
    // Obtener datos de miembro para copiar
    public function obtenerDatosMiembro($miembro_id) {
        if (!$this->conexion) return null;
        
        $miembro_id = (int)$miembro_id;
        
        $sql = "SELECT nombres, apellidos, email, telefono, direccion, fecha_nacimiento 
                FROM miembros 
                WHERE id = $miembro_id";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $miembro = $resultado ? mysqli_fetch_assoc($resultado) : null;
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        return $miembro;
    }
    
    // Buscar estudiante por nombre con sus cursos
    public function buscarEstudianteConCursos($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        
        $sql = "SELECT DISTINCT e.id, e.nombres, e.apellidos, e.email, e.telefono, e.direccion, 
                       e.fecha_nacimiento, e.miembro_id, e.estado
                FROM estudiantes e
                WHERE (e.nombres LIKE '$like' OR e.apellidos LIKE '$like' OR CONCAT(e.nombres, ' ', e.apellidos) LIKE '$like')
                AND e.estado = 1
                ORDER BY e.apellidos, e.nombres
                LIMIT 50";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $estudiantes = [];
        
        if ($resultado) {
            while ($estudiante = mysqli_fetch_assoc($resultado)) {
                $estudiante_id = (int)$estudiante['id'];
                
                // Obtener cursos del estudiante
                $sql_cursos = "SELECT c.id, c.nombre, c.descripcion, c.fecha_inicio, c.fecha_fin,
                                      ec.fecha_inscripcion, ec.estado as inscripcion_estado
                               FROM estudiantes_cursos ec
                               INNER JOIN cursos c ON ec.curso_id = c.id
                               WHERE ec.estudiante_id = $estudiante_id AND ec.estado = 1
                               ORDER BY ec.fecha_inscripcion DESC";
                
                $resultado_cursos = mysqli_query($this->conexion, $sql_cursos);
                $estudiante['cursos'] = [];
                
                if ($resultado_cursos) {
                    while ($curso = mysqli_fetch_assoc($resultado_cursos)) {
                        $estudiante['cursos'][] = $curso;
                    }
                    mysqli_free_result($resultado_cursos);
                }
                
                $estudiantes[] = $estudiante;
            }
            mysqli_free_result($resultado);
        }
        
        return $estudiantes;
    }
    
    // Listar estudiantes inscritos en un curso específico
    public function listarPorCurso($curso_id) {
        if (!$this->conexion) return [];
        
        $curso_id = (int)$curso_id;
        
        $sql = "SELECT e.id, e.nombres, e.apellidos, e.fecha_nacimiento, e.telefono, e.email, 
                       e.direccion, e.miembro_id, e.estado,
                       ec.fecha_inscripcion, ec.nota_final
                FROM estudiantes_cursos ec
                INNER JOIN estudiantes e ON ec.estudiante_id = e.id
                WHERE ec.curso_id = $curso_id AND ec.estado = 1 AND e.estado = 1
                ORDER BY e.apellidos, e.nombres";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $estudiantes = [];
        
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $estudiantes[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $estudiantes;
    }
    
    // Obtener cursos en los que está inscrito un estudiante
    public function obtenerCursosPorEstudiante($estudiante_id) {
        if (!$this->conexion) return [];
        
        $estudiante_id = (int)$estudiante_id;
        
        $sql = "SELECT ec.curso_id, c.nombre, c.fecha_inicio, c.fecha_fin, ec.fecha_inscripcion, ec.estado
                FROM estudiantes_cursos ec
                INNER JOIN cursos c ON ec.curso_id = c.id
                WHERE ec.estudiante_id = $estudiante_id AND ec.estado = 1
                ORDER BY c.nombre";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $cursos = [];
        
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $cursos[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $cursos;
    }
}


