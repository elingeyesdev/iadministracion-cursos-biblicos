<?php
/**
 * ARCHIVO: dCurso.php
 * 
 * Este archivo contiene la clase dCurso que maneja todas las operaciones 
 * de acceso a datos relacionadas con cursos educativos en el sistema.
 * 
 * Funcionalidades principales:
 * - Crear nuevos cursos con docente asignado
 * - Actualizar información de cursos existentes
 * - Eliminar cursos (soft delete)
 * - Listar todos los cursos con información del docente
 * - Buscar cursos por ID o por nombre
 * - Listar cursos por docente específico
 * - Listar solo cursos activos
 * - Contar estudiantes inscritos en un curso
 * - Obtener estadísticas generales de cursos (activos, inactivos, vencidos)
 * - Buscar cursos por nombre con sus estudiantes incluidos
 * 
 * La clase gestiona la relación entre cursos, docentes y estudiantes,
 * permitiendo consultas complejas que incluyen información relacionada.
 * 
 * Proporciona métodos útiles para el seguimiento de cursos incluyendo
 * fechas de inicio y fin, capacidad máxima, estado y docente asignado.
 */
require_once 'dConexion.php';

class dCurso {
    private $conexion;
    
    // Atributos de la entidad Curso
    private $id;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $capacidad_maxima;
    private $docente_id;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombre = null, $descripcion = null, $fecha_inicio = null, $fecha_fin = null, $capacidad_maxima = null, $docente_id = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->nombre = $nombre;
            $this->descripcion = $descripcion;
            $this->fecha_inicio = $fecha_inicio;
            $this->fecha_fin = $fecha_fin;
            $this->capacidad_maxima = (int)$capacidad_maxima;
            $this->docente_id = $docente_id ? (int)$docente_id : null;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($nombre, $descripcion, $fecha_inicio, $fecha_fin, $capacidad_maxima, $docente_id, $estado = 1) {
        if (!$this->conexion) return false;
        
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $fecha_inicio = mysqli_real_escape_string($this->conexion, $fecha_inicio);
        $fecha_fin = mysqli_real_escape_string($this->conexion, $fecha_fin);
        $capacidad_maxima = (int)$capacidad_maxima;
        $docente_id = (int)$docente_id;
        $estado = (int)$estado;
        
        $sql = "INSERT INTO cursos (nombre, descripcion, fecha_inicio, fecha_fin, capacidad_maxima, docente_id, estado) 
                VALUES ('$nombre', '$descripcion', '$fecha_inicio', '$fecha_fin', $capacidad_maxima, $docente_id, $estado)";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $id = $resultado ? mysqli_insert_id($this->conexion) : false;
        
        return $id;
    }
    
    public function actualizar($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $capacidad_maxima, $docente_id, $estado = 1) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $fecha_inicio = mysqli_real_escape_string($this->conexion, $fecha_inicio);
        $fecha_fin = mysqli_real_escape_string($this->conexion, $fecha_fin);
        $capacidad_maxima = (int)$capacidad_maxima;
        $docente_id = (int)$docente_id;
        $estado = (int)$estado;
        
        $sql = "UPDATE cursos 
                SET nombre = '$nombre', descripcion = '$descripcion', fecha_inicio = '$fecha_inicio', 
                    fecha_fin = '$fecha_fin', capacidad_maxima = $capacidad_maxima, docente_id = $docente_id, estado = $estado 
                WHERE id = $id";
        
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $sql = "UPDATE cursos SET estado = 0 WHERE id = $id";
        
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                    c.id, c.nombre, c.descripcion, c.fecha_inicio, c.fecha_fin, c.capacidad_maxima, c.estado,
                    d.nombres as docente_nombre, d.apellidos as docente_apellido
                FROM cursos c 
                LEFT JOIN docentes d ON c.docente_id = d.id 
                ORDER BY c.fecha_inicio DESC, c.nombre";
        
        $result = mysqli_query($this->conexion, $sql);
        $cursos = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $cursos[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $cursos;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "SELECT 
                    c.id, c.nombre, c.descripcion, c.fecha_inicio, c.fecha_fin, c.capacidad_maxima, c.estado, c.docente_id,
                    d.nombres as docente_nombre, d.apellidos as docente_apellido
                FROM cursos c 
                LEFT JOIN docentes d ON c.docente_id = d.id 
                WHERE c.id = $id";
        
        $result = mysqli_query($this->conexion, $sql);
        $curso = null;
        
        if ($result && mysqli_num_rows($result) > 0) {
            $curso = mysqli_fetch_assoc($result);
        }
        
        if ($result) mysqli_free_result($result);
        return $curso;
    }
    
    public function listarPorDocente($id_docente) {
        if (!$this->conexion) return [];
        
        $id_docente = (int)$id_docente;
        $sql = "SELECT 
                    c.id, c.nombre, c.descripcion, c.fecha_inicio, c.fecha_fin, c.capacidad_maxima, c.estado,
                    d.nombres as docente_nombre, d.apellidos as docente_apellido
                FROM cursos c 
                LEFT JOIN docentes d ON c.docente_id = d.id 
                WHERE c.docente_id = $id_docente 
                ORDER BY c.fecha_inicio DESC, c.nombre";
        
        $result = mysqli_query($this->conexion, $sql);
        $cursos = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $cursos[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $cursos;
    }
    
    public function listarActivos() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                    c.id, c.nombre, c.descripcion, c.fecha_inicio, c.fecha_fin, c.capacidad_maxima, c.estado, c.docente_id,
                    d.nombres as docente_nombre, d.apellidos as docente_apellido
                FROM cursos c 
                LEFT JOIN docentes d ON c.docente_id = d.id 
                WHERE c.estado = 1
                ORDER BY c.fecha_inicio DESC, c.nombre";
        
        $result = mysqli_query($this->conexion, $sql);
        $cursos = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $cursos[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $cursos;
    }
    
    public function contarEstudiantesPorCurso($id_curso) {
        if (!$this->conexion) return 0;
        
        $id_curso = (int)$id_curso;
        $sql = "SELECT COUNT(*) as total FROM estudiantes_cursos WHERE curso_id = $id_curso AND estado = 1";
        
        $result = mysqli_query($this->conexion, $sql);
        $total = 0;
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $total = (int)$row['total'];
        }
        
        if ($result) mysqli_free_result($result);
        return $total;
    }
    
    public function obtenerEstadisticasGenerales() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                COUNT(*) as total_cursos,
                COUNT(CASE WHEN estado = 1 THEN 1 END) as cursos_activos,
                COUNT(CASE WHEN estado = 0 THEN 1 END) as cursos_inactivos,
                COUNT(CASE WHEN fecha_fin < CURDATE() AND estado = 1 THEN 1 END) as cursos_vencidos
                FROM cursos";
        
        $result = mysqli_query($this->conexion, $sql);
        $estadisticas = [];
        
        if ($result && mysqli_num_rows($result) > 0) {
            $estadisticas = mysqli_fetch_assoc($result);
        }
        
        if ($result) mysqli_free_result($result);
        return $estadisticas;
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT 
                    c.id, c.nombre, c.descripcion, c.fecha_inicio, c.fecha_fin, c.capacidad_maxima, c.estado,
                    d.nombres as docente_nombre, d.apellidos as docente_apellido
                FROM cursos c 
                LEFT JOIN docentes d ON c.docente_id = d.id 
                WHERE c.nombre LIKE '$like'
                ORDER BY c.fecha_inicio DESC, c.nombre";
        $result = mysqli_query($this->conexion, $sql);
        $cursos = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $cursos[] = $row;
            }
            mysqli_free_result($result);
        }
        return $cursos;
    }
    
    // Buscar curso por nombre con sus estudiantes
    public function buscarCursoConEstudiantes($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        
        $sql = "SELECT DISTINCT c.id, c.nombre, c.descripcion, c.fecha_inicio, c.fecha_fin, 
                       c.capacidad_maxima, c.estado, c.docente_id,
                       d.nombres as docente_nombre, d.apellidos as docente_apellido
                FROM cursos c
                LEFT JOIN docentes d ON c.docente_id = d.id
                WHERE c.nombre LIKE '$like' AND c.estado = 1
                ORDER BY c.fecha_inicio DESC, c.nombre
                LIMIT 50";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $cursos = [];
        
        if ($resultado) {
            while ($curso = mysqli_fetch_assoc($resultado)) {
                $curso_id = (int)$curso['id'];
                
                // Obtener estudiantes del curso
                $sql_estudiantes = "SELECT e.id, e.nombres, e.apellidos, e.email, e.telefono,
                                           ec.fecha_inscripcion, ec.estado as inscripcion_estado
                                    FROM estudiantes_cursos ec
                                    INNER JOIN estudiantes e ON ec.estudiante_id = e.id
                                    WHERE ec.curso_id = $curso_id AND ec.estado = 1 AND e.estado = 1
                                    ORDER BY e.apellidos, e.nombres";
                
                $resultado_estudiantes = mysqli_query($this->conexion, $sql_estudiantes);
                $curso['estudiantes'] = [];
                
                if ($resultado_estudiantes) {
                    while ($estudiante = mysqli_fetch_assoc($resultado_estudiantes)) {
                        $curso['estudiantes'][] = $estudiante;
                    }
                    mysqli_free_result($resultado_estudiantes);
                }
                
                $cursos[] = $curso;
            }
            mysqli_free_result($resultado);
        }
        
        return $cursos;
    }
}