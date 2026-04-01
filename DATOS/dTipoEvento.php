<?php
require_once 'dConexion.php';

class dTipoEvento {
    private $conexion;
    
    // Atributos de la entidad TipoEvento
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
    
    public function insertar($nombre, $descripcion) {
        if (!$this->conexion) return false;
        
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        
        $sql = "INSERT INTO tipos_eventos (nombre, descripcion, estado) VALUES ('$nombre', '$descripcion', 1)";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function actualizar($id, $nombre, $descripcion, $estado = null) {
        if (!$this->conexion) return false;
        
        $id = mysqli_real_escape_string($this->conexion, $id);
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $setEstado = '';
        if ($estado !== null && $estado !== '') {
            $estadoBool = ($estado === '1' || $estado === 1 || $estado === 'activo') ? 1 : 0;
            $setEstado = ", estado = $estadoBool";
        }
        
        $sql = "UPDATE tipos_eventos SET nombre = '$nombre', descripcion = '$descripcion' $setEstado WHERE id = '$id'";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = mysqli_real_escape_string($this->conexion, $id);
        
        $sql_verificar = "SELECT COUNT(*) as total FROM eventos WHERE tipo_evento_id = '$id'";
        $resultado_verificar = mysqli_query($this->conexion, $sql_verificar);
        $total = $resultado_verificar ? mysqli_fetch_assoc($resultado_verificar) : ['total' => 0];
        if ($resultado_verificar) {
            mysqli_free_result($resultado_verificar);
        }
        
        if ($total['total'] > 0) {
            return false;
        }
        // Borrado lógico
        $sql = "UPDATE tipos_eventos SET estado = 0 WHERE id = '$id'";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT id, nombre, descripcion, estado FROM tipos_eventos WHERE estado = 1 ORDER BY nombre";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $tipos = array();
        if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $tipos[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $tipos;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = mysqli_real_escape_string($this->conexion, $id);
        $sql = "SELECT id, nombre, descripcion, estado FROM tipos_eventos WHERE id = '$id'";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $tipo = $resultado ? mysqli_fetch_assoc($resultado) : null;
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        
        return $tipo;
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT id, nombre, descripcion, estado FROM tipos_eventos WHERE nombre LIKE '$like' AND estado = 1 ORDER BY nombre";
        $resultado = mysqli_query($this->conexion, $sql);
        $tipos = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $tipos[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        return $tipos;
    }
}