<?php
require_once 'dConexion.php';

class dTipoTransaccion {
    private $conexion;
    
    // Atributos de la entidad TipoTransaccion
    private $id;
    private $nombre;
    private $tipo;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombre = null, $tipo = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->nombre = $nombre;
            $this->tipo = $tipo;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($nombre, $tipo) {
        if (!$this->conexion) return false;
        
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $tipo = mysqli_real_escape_string($this->conexion, $tipo);
        
        $sql = "INSERT INTO tipos_transacciones (nombre, tipo, estado) VALUES ('$nombre', '$tipo', 1)";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function actualizar($id, $nombre, $tipo, $estado = null) {
        if (!$this->conexion) return false;
        
        $id = mysqli_real_escape_string($this->conexion, $id);
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $tipo = mysqli_real_escape_string($this->conexion, $tipo);
        $setEstado = '';
        if ($estado !== null && $estado !== '') {
            $estadoBool = ($estado === '1' || $estado === 1 || $estado === 'activo') ? 1 : 0;
            $setEstado = ", estado = $estadoBool";
        }
        
        $sql = "UPDATE tipos_transacciones SET nombre = '$nombre', tipo = '$tipo' $setEstado WHERE id = '$id'";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = mysqli_real_escape_string($this->conexion, $id);
        
        $sql_verificar = "SELECT COUNT(*) as total FROM finanzas WHERE tipo_transaccion_id = '$id'";
        $resultado_verificar = mysqli_query($this->conexion, $sql_verificar);
        $total = $resultado_verificar ? mysqli_fetch_assoc($resultado_verificar) : ['total' => 0];
        if ($resultado_verificar) {
            mysqli_free_result($resultado_verificar);
        }
        
        if ($total['total'] > 0) {
            return false;
        }
        
        // Borrado lógico
        $sql = "UPDATE tipos_transacciones SET estado = 0 WHERE id = '$id'";
        $resultado = mysqli_query($this->conexion, $sql);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT id, nombre, tipo, estado FROM tipos_transacciones WHERE estado = 1 ORDER BY nombre";
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
        $sql = "SELECT id, nombre, tipo, estado FROM tipos_transacciones WHERE id = '$id'";
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
        $sql = "SELECT id, nombre, tipo, estado FROM tipos_transacciones 
                WHERE estado = 1 AND nombre LIKE '$like' 
                ORDER BY nombre";
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