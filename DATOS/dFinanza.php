<?php
require_once 'dConexion.php';

class dFinanza {
    private $conexion;
    
    // Atributos de la entidad Finanza
    private $id;
    private $fecha;
    private $tipo_transaccion_id;
    private $concepto;
    private $monto;
    private $evento_id;
    private $miembro_id;
    private $referencia;
    private $descripcion;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $fecha = null, $tipo_transaccion_id = null, $concepto = null, $monto = null, $evento_id = null, $miembro_id = null, $referencia = null, $descripcion = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->fecha = $fecha;
            $this->tipo_transaccion_id = $tipo_transaccion_id ? (int)$tipo_transaccion_id : null;
            $this->concepto = $concepto;
            $this->monto = (float)$monto;
            $this->evento_id = $evento_id ? (int)$evento_id : null;
            $this->miembro_id = $miembro_id ? (int)$miembro_id : null;
            $this->referencia = $referencia;
            $this->descripcion = $descripcion;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($fecha, $tipo_transaccion_id, $concepto, $monto, $evento_id = null, $miembro_id = null, $referencia = null, $descripcion = null) {
        if (!$this->conexion) return false;
        
        $fecha = mysqli_real_escape_string($this->conexion, $fecha);
        $tipo_transaccion_id = (int)$tipo_transaccion_id;
        $concepto = mysqli_real_escape_string($this->conexion, $concepto);
        $monto = (float)$monto;
        $evento_id = $evento_id ? (int)$evento_id : 'NULL';
        $miembro_id = $miembro_id ? (int)$miembro_id : 'NULL';
        $referencia = $referencia ? "'" . mysqli_real_escape_string($this->conexion, $referencia) . "'" : 'NULL';
        $descripcion = $descripcion ? "'" . mysqli_real_escape_string($this->conexion, $descripcion) . "'" : 'NULL';
        
        $sql = "CALL sp_finanza_insertar('$fecha', $tipo_transaccion_id, '$concepto', $monto, " . 
               ($evento_id !== 'NULL' ? $evento_id : 'NULL') . ", " . 
               ($miembro_id !== 'NULL' ? $miembro_id : 'NULL') . ", " . 
               ($referencia !== 'NULL' ? $referencia : 'NULL') . ", " . 
               ($descripcion !== 'NULL' ? $descripcion : 'NULL') . ")";
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            $row = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
            return $row['id'];
        }
        
        return false;
    }
    
    public function actualizar($id, $fecha, $tipo_transaccion_id, $concepto, $monto, $evento_id = null, $miembro_id = null, $referencia = null, $descripcion = null, $estado = 1) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $fecha = mysqli_real_escape_string($this->conexion, $fecha);
        $tipo_transaccion_id = (int)$tipo_transaccion_id;
        $concepto = mysqli_real_escape_string($this->conexion, $concepto);
        $monto = (float)$monto;
        $evento_id = $evento_id ? (int)$evento_id : 'NULL';
        $miembro_id = $miembro_id ? (int)$miembro_id : 'NULL';
        $referencia = $referencia ? "'" . mysqli_real_escape_string($this->conexion, $referencia) . "'" : 'NULL';
        $descripcion = $descripcion ? "'" . mysqli_real_escape_string($this->conexion, $descripcion) . "'" : 'NULL';
        $estado = (int)$estado;
        
        $sql = "CALL sp_finanza_actualizar($id, '$fecha', $tipo_transaccion_id, '$concepto', $monto, " . 
               ($evento_id !== 'NULL' ? $evento_id : 'NULL') . ", " . 
               ($miembro_id !== 'NULL' ? $miembro_id : 'NULL') . ", " . 
               ($referencia !== 'NULL' ? $referencia : 'NULL') . ", " . 
               ($descripcion !== 'NULL' ? $descripcion : 'NULL') . ", $estado)";
        $resultado = mysqli_query($this->conexion, $sql);
        mysqli_next_result($this->conexion);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $sql = "CALL sp_finanza_eliminar($id)";
        $resultado = mysqli_query($this->conexion, $sql);
        mysqli_next_result($this->conexion);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "CALL sp_finanza_listar()";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $finanzas = array();
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $finanzas[] = $fila;
            }
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
        }
        
        return $finanzas;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "CALL sp_finanza_buscar_por_id($id)";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $finanza = $resultado ? mysqli_fetch_assoc($resultado) : null;
        if ($resultado) {
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
        }
        
        return $finanza;
    }
    
    public function buscarPorConcepto($termino, $fecha_inicio = null, $fecha_fin = null) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        
        $sql = "SELECT f.*, tt.nombre as tipo_transaccion, tt.tipo, 
                       m.nombres as miembro_nombre, m.apellidos as miembro_apellido,
                       e.nombre as evento_nombre
                FROM finanzas f
                LEFT JOIN tipos_transacciones tt ON f.tipo_transaccion_id = tt.id
                LEFT JOIN miembros m ON f.miembro_id = m.id
                LEFT JOIN eventos e ON f.evento_id = e.id
                WHERE f.concepto LIKE '$like' AND f.estado = 1";
        
        if ($fecha_inicio) {
            $fecha_inicio = mysqli_real_escape_string($this->conexion, $fecha_inicio);
            $sql .= " AND f.fecha >= '$fecha_inicio'";
        }
        
        if ($fecha_fin) {
            $fecha_fin = mysqli_real_escape_string($this->conexion, $fecha_fin);
            $sql .= " AND f.fecha <= '$fecha_fin'";
        }
        
        $sql .= " ORDER BY f.fecha DESC, f.id DESC";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $transacciones = [];
        
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $transacciones[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $transacciones;
    }
    
    public function obtenerBalanceMensual($fecha_inicio, $fecha_fin) {
        if (!$this->conexion) return ['total_ingresos' => 0, 'total_egresos' => 0, 'balance' => 0];
        
        $fecha_inicio = mysqli_real_escape_string($this->conexion, $fecha_inicio);
        $fecha_fin = mysqli_real_escape_string($this->conexion, $fecha_fin);
        
        // Intentar usar el stored procedure primero
        $sql = "CALL sp_finanza_balance_mensual('$fecha_inicio', '$fecha_fin')";
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            $balance = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
            
            if ($balance && (isset($balance['total_ingresos']) || isset($balance['total_egresos']))) {
                return [
                    'total_ingresos' => (float)($balance['total_ingresos'] ?? 0),
                    'total_egresos' => (float)($balance['total_egresos'] ?? 0),
                    'balance' => (float)($balance['balance'] ?? 0)
                ];
            }
        }
        
        // Fallback: consulta directa si el stored procedure no funciona
        $sql_directo = "SELECT 
            COALESCE(SUM(CASE WHEN tt.tipo = 'ingreso' THEN f.monto ELSE 0 END), 0) as total_ingresos,
            COALESCE(SUM(CASE WHEN tt.tipo = 'egreso' THEN f.monto ELSE 0 END), 0) as total_egresos,
            COALESCE(SUM(CASE WHEN tt.tipo = 'ingreso' THEN f.monto WHEN tt.tipo = 'egreso' THEN -f.monto ELSE 0 END), 0) as balance
        FROM finanzas f
        INNER JOIN tipos_transacciones tt ON f.tipo_transaccion_id = tt.id
        WHERE f.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' AND f.estado = 1";
        
        $resultado_directo = mysqli_query($this->conexion, $sql_directo);
        
        if ($resultado_directo) {
            $balance = mysqli_fetch_assoc($resultado_directo);
            mysqli_free_result($resultado_directo);
            
            return [
                'total_ingresos' => (float)($balance['total_ingresos'] ?? 0),
                'total_egresos' => (float)($balance['total_egresos'] ?? 0),
                'balance' => (float)($balance['balance'] ?? 0)
            ];
        }
        
        // Si todo falla, retornar ceros
        return ['total_ingresos' => 0, 'total_egresos' => 0, 'balance' => 0];
    }
    
    public function obtenerReporteTransacciones($fecha_inicio = null, $fecha_fin = null, $tipo_transaccion_id = null) {
        if (!$this->conexion) return [];
        
        $sql = "SELECT f.*, tt.nombre as tipo_transaccion, tt.tipo, 
                       m.nombres as miembro_nombre, m.apellidos as miembro_apellido,
                       e.nombre as evento_nombre
                FROM finanzas f
                LEFT JOIN tipos_transacciones tt ON f.tipo_transaccion_id = tt.id
                LEFT JOIN miembros m ON f.miembro_id = m.id
                LEFT JOIN eventos e ON f.evento_id = e.id
                WHERE f.estado = 1";
        
        if ($fecha_inicio) {
            $fecha_inicio = mysqli_real_escape_string($this->conexion, $fecha_inicio);
            $sql .= " AND f.fecha >= '$fecha_inicio'";
        }
        
        if ($fecha_fin) {
            $fecha_fin = mysqli_real_escape_string($this->conexion, $fecha_fin);
            $sql .= " AND f.fecha <= '$fecha_fin'";
        }
        
        if ($tipo_transaccion_id) {
            $tipo_transaccion_id = (int)$tipo_transaccion_id;
            $sql .= " AND f.tipo_transaccion_id = $tipo_transaccion_id";
        }
        
        $sql .= " ORDER BY f.fecha DESC, f.id DESC";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $transacciones = [];
        
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $transacciones[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $transacciones;
    }
}
