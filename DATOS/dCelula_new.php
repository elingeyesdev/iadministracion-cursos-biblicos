<?php
require_once 'dConexion.php';

class dCelula {
    private $conexion;
    
    // Atributos de la entidad Celula
    private $id;
    private $nombre;
    private $direccion;
    private $dia_reunion;
    private $hora_reunion;
    private $lider_id;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombre = null, $direccion = null, $dia_reunion = null, $hora_reunion = null, $lider_id = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->nombre = $nombre;
            $this->direccion = $direccion;
            $this->dia_reunion = $dia_reunion;
            $this->hora_reunion = $hora_reunion;
            $this->lider_id = $lider_id ? (int)$lider_id : null;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($nombre, $direccion, $dia_reunion, $hora_reunion, $lider_id = null) {
        if (!$this->conexion) return false;
        
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        $dia_reunion = mysqli_real_escape_string($this->conexion, $dia_reunion);
        $hora_reunion = mysqli_real_escape_string($this->conexion, $hora_reunion);
        $lider_id = $lider_id ? (int)$lider_id : 'NULL';
        
        $sql = "CALL sp_celula_insertar('$nombre', '$direccion', '$dia_reunion', '$hora_reunion', " . ($lider_id !== 'NULL' ? $lider_id : 'NULL') . ")";
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            $row = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
            return $row['id'];
        }
        
        return false;
    }
    
    public function actualizar($id, $nombre, $direccion, $dia_reunion, $hora_reunion, $estado, $lider_id = null) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        $dia_reunion = mysqli_real_escape_string($this->conexion, $dia_reunion);
        $hora_reunion = mysqli_real_escape_string($this->conexion, $hora_reunion);
        $estado = (int)$estado;
        $lider_id = $lider_id ? (int)$lider_id : 'NULL';
        
        $sql = "CALL sp_celula_actualizar($id, '$nombre', '$direccion', '$dia_reunion', '$hora_reunion', $estado, " . ($lider_id !== 'NULL' ? $lider_id : 'NULL') . ")";
        $resultado = mysqli_query($this->conexion, $sql);
        mysqli_next_result($this->conexion);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        
        // Verificar si hay miembros asociados
        $sql_check = "SELECT COUNT(*) as total FROM miembros_celulas WHERE celula_id = $id";
        $resultado_check = mysqli_query($this->conexion, $sql_check);
        $row = $resultado_check ? mysqli_fetch_assoc($resultado_check) : ['total' => 0];
        if ($resultado_check) {
            mysqli_free_result($resultado_check);
        }
        
        if ($row['total'] > 0) {
            return false;
        }
        
        $sql = "CALL sp_celula_eliminar($id)";
        $resultado = mysqli_query($this->conexion, $sql);
        mysqli_next_result($this->conexion);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "CALL sp_celula_listar()";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $celulas = array();
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $celulas[] = $fila;
            }
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
        }
        
        return $celulas;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "CALL sp_celula_buscar_por_id($id)";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $celula = $resultado ? mysqli_fetch_assoc($resultado) : null;
        if ($resultado) {
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
        }
        
        return $celula;
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT c.id, c.nombre, c.direccion, c.dia_reunion, c.hora_reunion, c.estado,
                       m.nombres as nombre_lider, m.apellidos as apellido_lider
                FROM celulas c
                LEFT JOIN miembros m ON c.lider_id = m.id
                WHERE c.nombre LIKE '$like'
                ORDER BY c.nombre";
        $resultado = mysqli_query($this->conexion, $sql);
        $celulas = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $celulas[] = $fila;
        }
            mysqli_free_result($resultado);
        }
        return $celulas;
    }
    
    public function contarCelulasActivas() {
        if (!$this->conexion) return 0;
        
        $sql = "CALL sp_celula_contar_activas()";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $total = $resultado ? mysqli_fetch_assoc($resultado) : ['total' => 0];
        if ($resultado) {
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
        }
        
        return $total['total'];
    }
    
    // Métodos para gestionar miembros de la célula
    public function agregarMiembro($celula_id, $miembro_id, $cargo = null) {
        if (!$this->conexion) return false;
        
        $celula_id = (int)$celula_id;
        $miembro_id = (int)$miembro_id;
        $cargo = $cargo ? "'" . mysqli_real_escape_string($this->conexion, $cargo) . "'" : 'NULL';
        
        $sql = "INSERT INTO miembros_celulas (miembro_id, celula_id, cargo, fecha_ingreso) 
                VALUES ($miembro_id, $celula_id, $cargo, CURDATE())
                ON DUPLICATE KEY UPDATE cargo = $cargo, estado = 1";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return (bool)$resultado;
    }
    
    public function eliminarMiembro($celula_id, $miembro_id) {
        if (!$this->conexion) return false;
        
        $celula_id = (int)$celula_id;
        $miembro_id = (int)$miembro_id;
        
        $sql = "DELETE FROM miembros_celulas WHERE celula_id = $celula_id AND miembro_id = $miembro_id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return (bool)$resultado;
    }
    
    public function listarMiembros($celula_id) {
        if (!$this->conexion) return [];
        
        $celula_id = (int)$celula_id;
        $sql = "SELECT mc.id, mc.miembro_id, mc.cargo, mc.fecha_ingreso, mc.estado,
                       m.nombres, m.apellidos, m.email, m.telefono
                FROM miembros_celulas mc
                INNER JOIN miembros m ON mc.miembro_id = m.id
                WHERE mc.celula_id = $celula_id AND mc.estado = 1
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
    
    public function actualizarMiembros($celula_id, $miembros_ids) {
        if (!$this->conexion) return false;
        
        $celula_id = (int)$celula_id;
        
        // Eliminar todos los miembros actuales
        $sql_delete = "DELETE FROM miembros_celulas WHERE celula_id = $celula_id";
        mysqli_query($this->conexion, $sql_delete);
        
        // Agregar los nuevos miembros
        if (is_array($miembros_ids) && count($miembros_ids) > 0) {
            foreach ($miembros_ids as $miembro_id) {
                $miembro_id = (int)$miembro_id;
                if ($miembro_id > 0) {
                    $this->agregarMiembro($celula_id, $miembro_id);
                }
            }
        }
        
        return true;
    }
}