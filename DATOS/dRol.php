<?php
require_once 'dConexion.php';

class dRol {
    private $conexion;
    
    // Atributos de la entidad Rol
    private $id;
    private $nombre;
    private $descripcion;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombre = null, $descripcion = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->nombre = $nombre;
            $this->descripcion = $descripcion;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($nombre, $descripcion, $estado = 1) {
        if (!$this->conexion) return false;
        
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $estado = (int)$estado;
        
        $sql = "INSERT INTO roles (nombre, descripcion, estado) 
                VALUES ('$nombre', '$descripcion', $estado)";
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            $id = mysqli_insert_id($this->conexion);
            return $id;
        }
        
        return false;
    }
    
    public function actualizar($id, $nombre, $descripcion, $estado) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $estado = (int)$estado;
        
        $sql = "UPDATE roles 
                SET nombre = '$nombre', descripcion = '$descripcion', estado = $estado 
                WHERE id = $id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        
        // Verificar si hay usuarios con este rol
        $sql_check = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id = $id";
        $resultado_check = mysqli_query($this->conexion, $sql_check);
        $row = $resultado_check ? mysqli_fetch_assoc($resultado_check) : ['total' => 0];
        if ($resultado_check) {
            mysqli_free_result($resultado_check);
        }
        
        if ($row['total'] > 0) {
            return false; // No se puede eliminar si hay usuarios con este rol
        }
        
        $sql = "UPDATE roles SET estado = 0 WHERE id = $id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) {
            error_log("dRol->listar(): No hay conexión a la base de datos");
            return [];
        }
        
        $sql = "SELECT id, nombre, descripcion, estado, fecha_creacion 
                FROM roles 
                ORDER BY nombre";
        $resultado = mysqli_query($this->conexion, $sql);
        
        if (!$resultado) {
            error_log("dRol->listar(): Error en consulta SQL: " . mysqli_error($this->conexion));
            return [];
        }
        
        $roles = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $roles[] = $fila;
        }
        mysqli_free_result($resultado);
        
        return $roles;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "SELECT id, nombre, descripcion, estado, fecha_creacion 
                FROM roles 
                WHERE id = $id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $rol = $resultado ? mysqli_fetch_assoc($resultado) : null;
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        
        return $rol;
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT id, nombre, descripcion, estado, fecha_creacion
                FROM roles
                WHERE nombre LIKE '$like' AND estado = 1
                ORDER BY nombre";
        $resultado = mysqli_query($this->conexion, $sql);
        $roles = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $roles[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        return $roles;
    }
    
    public function obtenerPrivilegios($rol_id) {
        if (!$this->conexion) return [];
        
        $rol_id = (int)$rol_id;
        $sql = "SELECT p.id, p.nombre, p.descripcion, p.modulo, p.accion 
                FROM privilegios p
                INNER JOIN roles_privilegios rp ON p.id = rp.privilegio_id
                WHERE rp.rol_id = $rol_id AND p.estado = 1
                ORDER BY p.modulo, p.accion";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $privilegios = array();
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $privilegios[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $privilegios;
    }
    
    public function asignarPrivilegios($rol_id, $privilegios_ids) {
        if (!$this->conexion) return false;
        
        $rol_id = (int)$rol_id;
        
        // Eliminar privilegios actuales
        $sql_delete = "DELETE FROM roles_privilegios WHERE rol_id = $rol_id";
        mysqli_query($this->conexion, $sql_delete);
        
        // Insertar nuevos privilegios
        if (!empty($privilegios_ids) && is_array($privilegios_ids)) {
            foreach ($privilegios_ids as $privilegio_id) {
                $privilegio_id = (int)$privilegio_id;
                $sql = "INSERT INTO roles_privilegios (rol_id, privilegio_id) 
                        VALUES ($rol_id, $privilegio_id)";
                mysqli_query($this->conexion, $sql);
            }
        }
        
        return true;
    }
    
    public function listarPrivilegios() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT id, nombre, descripcion, modulo, accion 
                FROM privilegios 
                WHERE estado = 1 
                ORDER BY modulo, accion";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $privilegios = array();
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $privilegios[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $privilegios;
    }
}

