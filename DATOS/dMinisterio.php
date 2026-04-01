<?php
require_once 'dConexion.php';

class dMinisterio {
    private $conexion;
    
    // Atributos de la entidad Ministerio
    private $id;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $lider_id;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombre = null, $descripcion = null, $fecha_inicio = null, $lider_id = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->nombre = $nombre;
            $this->descripcion = $descripcion;
            $this->fecha_inicio = $fecha_inicio;
            $this->lider_id = $lider_id ? (int)$lider_id : null;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($nombre, $descripcion, $lider_id) {
        if (!$this->conexion) return false;
        
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $lider_id_sql = ($lider_id === null || $lider_id === '') ? 'NULL' : (int)$lider_id;
        
        $sql = "CALL sp_ministerio_insertar('$nombre', '$descripcion', CURDATE(), $lider_id_sql)";
        $resultado = mysqli_query($this->conexion, $sql);
        if ($resultado === false) {
            error_log("Error en sp_ministerio_insertar: " . mysqli_error($this->conexion));
            return false;
        }
        if ($resultado instanceof mysqli_result) {
            $row = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
            return $row && isset($row['id']) ? (int)$row['id'] : false;
        }
        
        return false;
    }
    
    public function actualizar($id, $nombre, $descripcion, $lider_id, $estado) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $lider_id_sql = ($lider_id === null || $lider_id === '') ? 'NULL' : (int)$lider_id;
        $estado_bool = null;
        if ($estado !== null && $estado !== '') {
            $estado_bool = ($estado === 'activo' || $estado === 1 || $estado === '1') ? 1 : 0;
        }
        
        // Actualizar datos principales vía SP
        $sql1 = "CALL sp_ministerio_actualizar($id, '$nombre', '$descripcion', CURDATE(), $lider_id_sql)";
        $resultado = mysqli_query($this->conexion, $sql1);
        if ($resultado === false) {
            error_log("Error en sp_ministerio_actualizar: " . mysqli_error($this->conexion));
            return false;
        }
        // Liberar resultado solo si es un objeto mysqli_result
        if ($resultado instanceof mysqli_result) {
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
        }
        
        // Actualizar estado si se solicitó
        if ($estado_bool !== null) {
            $sql2 = "UPDATE ministerios SET estado = $estado_bool WHERE id = $id";
            $resultado2 = mysqli_query($this->conexion, $sql2);
            if (!$resultado2) {
                return false;
            }
        }
        
        return true;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $sql = "CALL sp_ministerio_eliminar($id)";
        $resultado = mysqli_query($this->conexion, $sql);
        if ($resultado) {
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
        }
        
        return (bool)$resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "CALL sp_ministerio_listar()";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $ministerios = array();
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $ministerios[] = $fila;
            }
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
        }
        
        return $ministerios;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "SELECT m.id, m.nombre, m.descripcion, m.fecha_inicio, m.estado, m.lider_id,
                       mi.nombres AS lider_nombre, mi.apellidos AS lider_apellido
                FROM ministerios m
                LEFT JOIN miembros mi ON m.lider_id = mi.id
                WHERE m.id = $id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $ministerio = $resultado ? mysqli_fetch_assoc($resultado) : null;
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        
        return $ministerio;
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT m.id, m.nombre, m.descripcion, m.fecha_inicio, m.estado, m.lider_id,
                       mi.nombres AS lider_nombre, mi.apellidos AS lider_apellido
                FROM ministerios m
                LEFT JOIN miembros mi ON m.lider_id = mi.id
                WHERE m.nombre LIKE '$like'
                ORDER BY m.nombre";
        $resultado = mysqli_query($this->conexion, $sql);
        $ministerios = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $ministerios[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        return $ministerios;
    }
    
    public function contarMinisteriosActivos() {
        if (!$this->conexion) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM ministerios WHERE estado = 1";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $total = $resultado ? mysqli_fetch_assoc($resultado) : ['total' => 0];
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        
        return $total['total'];
    }
    
    // Métodos para gestionar miembros del ministerio
    public function agregarMiembro($ministerio_id, $miembro_id, $cargo = null) {
        if (!$this->conexion) return false;
        
        $ministerio_id = (int)$ministerio_id;
        $miembro_id = (int)$miembro_id;
        $cargo = $cargo ? "'" . mysqli_real_escape_string($this->conexion, $cargo) . "'" : 'NULL';
        
        $sql = "INSERT INTO miembros_ministerios (miembro_id, ministerio_id, cargo, fecha_ingreso) 
                VALUES ($miembro_id, $ministerio_id, $cargo, CURDATE())
                ON DUPLICATE KEY UPDATE cargo = $cargo, estado = 1";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return (bool)$resultado;
    }
    
    public function eliminarMiembro($ministerio_id, $miembro_id) {
        if (!$this->conexion) return false;
        
        $ministerio_id = (int)$ministerio_id;
        $miembro_id = (int)$miembro_id;
        
        $sql = "DELETE FROM miembros_ministerios WHERE ministerio_id = $ministerio_id AND miembro_id = $miembro_id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return (bool)$resultado;
    }
    
    public function listarMiembros($ministerio_id) {
        if (!$this->conexion) return [];
        
        $ministerio_id = (int)$ministerio_id;
        $sql = "SELECT mm.id, mm.miembro_id, mm.cargo, mm.fecha_ingreso, mm.estado,
                       m.nombres, m.apellidos, m.email, m.telefono
                FROM miembros_ministerios mm
                INNER JOIN miembros m ON mm.miembro_id = m.id
                WHERE mm.ministerio_id = $ministerio_id AND mm.estado = 1
                ORDER BY m.nombres, m.apellidos";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $miembros = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $miembros[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $miembros;
    }
    
    public function actualizarMiembros($ministerio_id, $miembros_ids) {
        if (!$this->conexion) return false;
        
        $ministerio_id = (int)$ministerio_id;
        
        // Eliminar todos los miembros actuales
        $sql_delete = "DELETE FROM miembros_ministerios WHERE ministerio_id = $ministerio_id";
        mysqli_query($this->conexion, $sql_delete);
        
        // Agregar los nuevos miembros
        if (is_array($miembros_ids) && count($miembros_ids) > 0) {
            foreach ($miembros_ids as $miembro_id) {
                $miembro_id = (int)$miembro_id;
                if ($miembro_id > 0) {
                    $this->agregarMiembro($ministerio_id, $miembro_id);
                }
            }
        }
        
        return true;
    }
}