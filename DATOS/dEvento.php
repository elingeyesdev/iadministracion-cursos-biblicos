<?php
require_once 'dConexion.php';

class dEvento {
    private $conexion;
    
    // Atributos de la entidad Evento
    private $id;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $tipo_evento_id;
    private $es_economico;
    private $presupuesto;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombre = null, $descripcion = null, $fecha_inicio = null, $fecha_fin = null, $tipo_evento_id = null, $es_economico = 0, $presupuesto = 0, $estado = 1) {
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
            $this->tipo_evento_id = $tipo_evento_id ? (int)$tipo_evento_id : null;
            $this->es_economico = (int)$es_economico;
            $this->presupuesto = (float)$presupuesto;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($nombre, $descripcion, $fecha_inicio, $fecha_fin, $tipo_evento_id, $es_economico = 0, $presupuesto = 0) {
        if (!$this->conexion) return false;
        
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $fecha_inicio = mysqli_real_escape_string($this->conexion, $fecha_inicio);
        $fecha_fin = $fecha_fin ? "'" . mysqli_real_escape_string($this->conexion, $fecha_fin) . "'" : 'NULL';
        $tipo_evento_id = (int)$tipo_evento_id;
        $es_economico = (int)$es_economico;
        $presupuesto = (float)$presupuesto;
        
        $sql = "CALL sp_evento_insertar('$nombre', '$descripcion', '$fecha_inicio', $fecha_fin, $tipo_evento_id, $es_economico, $presupuesto)";
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            $row = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
            return $row['id'];
        }
        
        return false;
    }
    
    public function actualizar($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $tipo_evento_id, $es_economico, $presupuesto) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $nombre = mysqli_real_escape_string($this->conexion, $nombre);
        $descripcion = mysqli_real_escape_string($this->conexion, $descripcion);
        $fecha_inicio = mysqli_real_escape_string($this->conexion, $fecha_inicio);
        $fecha_fin = $fecha_fin ? "'" . mysqli_real_escape_string($this->conexion, $fecha_fin) . "'" : 'NULL';
        $tipo_evento_id = (int)$tipo_evento_id;
        $es_economico = (int)$es_economico;
        $presupuesto = (float)$presupuesto;
        
        $sql = "CALL sp_evento_actualizar($id, '$nombre', '$descripcion', '$fecha_inicio', $fecha_fin, $tipo_evento_id, $es_economico, $presupuesto)";
        $resultado = mysqli_query($this->conexion, $sql);
        mysqli_next_result($this->conexion);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $sql = "CALL sp_evento_eliminar($id)";
        $resultado = mysqli_query($this->conexion, $sql);
        mysqli_next_result($this->conexion);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT e.id, e.nombre, e.descripcion, e.fecha_inicio, e.fecha_fin, e.es_economico, e.presupuesto, e.estado, 
                       e.tipo_evento_id, te.nombre as tipo_evento_nombre
                FROM eventos e 
                LEFT JOIN tipos_eventos te ON e.tipo_evento_id = te.id 
                WHERE e.estado = 1
                ORDER BY e.fecha_inicio DESC";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $eventos = array();
        if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $eventos[] = $fila;
        }
            mysqli_free_result($resultado);
        }
        
        return $eventos;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "SELECT e.id, e.nombre, e.descripcion, e.fecha_inicio, e.fecha_fin, e.es_economico, e.presupuesto, e.estado, 
                       e.tipo_evento_id, te.nombre as tipo_evento_nombre
                FROM eventos e 
                LEFT JOIN tipos_eventos te ON e.tipo_evento_id = te.id 
                WHERE e.id = $id";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $evento = $resultado ? mysqli_fetch_assoc($resultado) : null;
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        
        return $evento;
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT e.id, e.nombre, e.descripcion, e.fecha_inicio, e.fecha_fin, 
                       te.nombre as tipo_evento, e.es_economico, e.presupuesto, e.estado
                FROM eventos e
                INNER JOIN tipos_eventos te ON e.tipo_evento_id = te.id
                WHERE e.estado = 1 AND e.nombre LIKE '$like'
                ORDER BY e.fecha_inicio DESC";
        $resultado = mysqli_query($this->conexion, $sql);
        $eventos = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $eventos[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        return $eventos;
    }
    
    public function buscarPorFecha($fecha) {
        if (!$this->conexion) return [];
        
        $fecha = mysqli_real_escape_string($this->conexion, $fecha);
        $sql = "SELECT e.id, e.nombre, e.descripcion, e.fecha_inicio, e.fecha_fin, e.es_economico, e.presupuesto, e.estado, 
                       e.tipo_evento_id, te.nombre as tipo_evento_nombre
                FROM eventos e 
                LEFT JOIN tipos_eventos te ON e.tipo_evento_id = te.id 
                WHERE e.fecha_inicio = '$fecha' AND e.estado = 1
                ORDER BY e.fecha_inicio ASC";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $eventos = array();
        if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $eventos[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $eventos;
    }
    
    public function contarEventosActivos() {
        if (!$this->conexion) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM eventos WHERE estado = 1 AND fecha_inicio >= CURDATE()";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $total = $resultado ? mysqli_fetch_assoc($resultado) : ['total' => 0];
        if ($resultado) {
            mysqli_free_result($resultado);
        }
        
        return $total['total'];
    }
}