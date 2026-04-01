<?php
require_once 'dConexion.php';

class dDocente {
    private $conexion;
    
    // Atributos de la entidad Docente
    private $id;
    private $miembro_id;
    private $nombres;
    private $apellidos;
    private $especialidad;
    private $telefono;
    private $email;
    private $direccion;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombres = null, $apellidos = null, $especialidad = null, $telefono = null, $email = null, $direccion = null, $miembro_id = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->nombres = $nombres;
            $this->apellidos = $apellidos;
            $this->especialidad = $especialidad;
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
    
    public function insertar($nombres, $apellidos, $especialidad, $telefono, $email, $direccion, $miembro_id = null, $estado = 1) {
        if (!$this->conexion) return false;
        
        $nombres = mysqli_real_escape_string($this->conexion, $nombres);
        $apellidos = mysqli_real_escape_string($this->conexion, $apellidos);
        $especialidad = mysqli_real_escape_string($this->conexion, $especialidad);
        $telefono = mysqli_real_escape_string($this->conexion, $telefono);
        $email = mysqli_real_escape_string($this->conexion, $email);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        $miembro_id = $miembro_id ? (int)$miembro_id : 'NULL';
        $estado = (int)$estado;
        
        $sql = "INSERT INTO docentes (nombres, apellidos, especialidad, telefono, email, direccion, miembro_id, estado) 
                VALUES ('$nombres', '$apellidos', '$especialidad', '$telefono', '$email', '$direccion', $miembro_id, $estado)";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $id = $resultado ? mysqli_insert_id($this->conexion) : false;
        
        return $id;
    }
    
    public function actualizar($id, $nombres, $apellidos, $especialidad, $telefono, $email, $direccion, $miembro_id = null, $estado = 1) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $nombres = mysqli_real_escape_string($this->conexion, $nombres);
        $apellidos = mysqli_real_escape_string($this->conexion, $apellidos);
        $especialidad = mysqli_real_escape_string($this->conexion, $especialidad);
        $telefono = mysqli_real_escape_string($this->conexion, $telefono);
        $email = mysqli_real_escape_string($this->conexion, $email);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        $miembro_id = $miembro_id ? (int)$miembro_id : 'NULL';
        $estado = (int)$estado;
        
        $sql = "UPDATE docentes SET nombres = '$nombres', apellidos = '$apellidos', especialidad = '$especialidad', 
                telefono = '$telefono', email = '$email', direccion = '$direccion', miembro_id = $miembro_id, estado = $estado 
                WHERE id = $id";
        
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $sql = "UPDATE docentes SET estado = 0 WHERE id = $id";
        
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT d.id, d.miembro_id, d.nombres, d.apellidos, d.especialidad, 
                       d.telefono, d.email, d.direccion, d.estado, d.fecha_creacion, d.fecha_actualizacion,
                       m.nombres as nombre_miembro, m.apellidos as apellido_miembro
                FROM docentes d 
                LEFT JOIN miembros m ON d.miembro_id = m.id 
                WHERE d.estado = 1
                ORDER BY d.apellidos, d.nombres";
        
        $result = mysqli_query($this->conexion, $sql);
        $docentes = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $docentes[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $docentes;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "SELECT d.id, d.miembro_id, d.nombres, d.apellidos, d.especialidad, 
                       d.telefono, d.email, d.direccion, d.estado, d.fecha_creacion, d.fecha_actualizacion,
                       m.nombres as nombre_miembro, m.apellidos as apellido_miembro
                FROM docentes d 
                LEFT JOIN miembros m ON d.miembro_id = m.id 
                WHERE d.id = $id";
        
        $result = mysqli_query($this->conexion, $sql);
        $docente = null;
        
        if ($result && mysqli_num_rows($result) > 0) {
            $docente = mysqli_fetch_assoc($result);
        }
        
        if ($result) mysqli_free_result($result);
        return $docente;
    }
    
    public function buscarPorIdMiembro($id_miembro) {
        if (!$this->conexion) return null;
        
        $id_miembro = (int)$id_miembro;
        $sql = "SELECT d.*, m.nombres as nombre_miembro, m.apellidos as apellido_miembro, 
                m.email, m.telefono, m.direccion 
                FROM docentes d 
                LEFT JOIN miembros m ON d.miembro_id = m.id 
                WHERE d.miembro_id = $id_miembro";
        
        $result = mysqli_query($this->conexion, $sql);
        $docente = null;
        
        if ($result && mysqli_num_rows($result) > 0) {
            $docente = mysqli_fetch_assoc($result);
        }
        
        if ($result) mysqli_free_result($result);
        return $docente;
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT d.id, d.miembro_id, d.nombres, d.apellidos, d.especialidad, 
                       d.telefono, d.email, d.direccion, d.estado, d.fecha_creacion, d.fecha_actualizacion,
                       m.nombres as nombre_miembro, m.apellidos as apellido_miembro
                FROM docentes d 
                LEFT JOIN miembros m ON d.miembro_id = m.id 
                WHERE CONCAT(d.nombres, ' ', d.apellidos) LIKE '$like' AND d.estado = 1
                ORDER BY d.apellidos, d.nombres";
        $result = mysqli_query($this->conexion, $sql);
        $docentes = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $docentes[] = $row;
            }
            mysqli_free_result($result);
        }
        return $docentes;
    }
    
    // Buscar docente por nombre con sus cursos
    public function buscarDocenteConCursos($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        
        $sql = "SELECT DISTINCT d.id, d.nombres, d.apellidos, d.especialidad, d.email, d.telefono, 
                       d.direccion, d.miembro_id, d.estado,
                       m.nombres as nombre_miembro, m.apellidos as apellido_miembro
                FROM docentes d
                LEFT JOIN miembros m ON d.miembro_id = m.id
                WHERE (CONCAT(d.nombres, ' ', d.apellidos) LIKE '$like' OR 
                       CONCAT(m.nombres, ' ', m.apellidos) LIKE '$like')
                AND d.estado = 1
                ORDER BY d.apellidos, d.nombres
                LIMIT 50";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $docentes = [];
        
        if ($resultado) {
            while ($docente = mysqli_fetch_assoc($resultado)) {
                $docente_id = (int)$docente['id'];
                
                // Obtener cursos del docente
                $sql_cursos = "SELECT c.id, c.nombre, c.descripcion, c.fecha_inicio, c.fecha_fin, 
                                      c.capacidad_maxima, c.estado
                               FROM cursos c
                               WHERE c.docente_id = $docente_id AND c.estado = 1
                               ORDER BY c.fecha_inicio DESC";
                
                $resultado_cursos = mysqli_query($this->conexion, $sql_cursos);
                $docente['cursos'] = [];
                
                if ($resultado_cursos) {
                    while ($curso = mysqli_fetch_assoc($resultado_cursos)) {
                        $docente['cursos'][] = $curso;
                    }
                    mysqli_free_result($resultado_cursos);
                }
                
                $docentes[] = $docente;
            }
            mysqli_free_result($resultado);
        }
        
        return $docentes;
    }
    
    public function contarCursosActivos($id_docente) {
        if (!$this->conexion) return 0;
        
        $id_docente = (int)$id_docente;
        $sql = "SELECT COUNT(*) as total FROM cursos WHERE docente_id = $id_docente AND estado = 1";
        
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
                COUNT(*) as total_docentes,
                COUNT(CASE WHEN estado = 1 THEN 1 END) as docentes_activos,
                COUNT(CASE WHEN estado = 0 THEN 1 END) as docentes_inactivos
                FROM docentes";
        
        $result = mysqli_query($this->conexion, $sql);
        $estadisticas = [];
        
        if ($result && mysqli_num_rows($result) > 0) {
            $estadisticas = mysqli_fetch_assoc($result);
        }
        
        if ($result) mysqli_free_result($result);
        return $estadisticas;
    }
}