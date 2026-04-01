<?php
require_once 'dConexion.php';

class dSolicitudEstudiante {
    private $conexion;
    
    public function __construct() {
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    /**
     * Crear una nueva solicitud con token único
     */
    public function crear($nombres, $apellidos, $fecha_nacimiento = null, $telefono = '', $email = '', $direccion = '') {
        if (!$this->conexion) return false;
        
        // Generar token único
        $token = bin2hex(random_bytes(32));
        
        $nombres = mysqli_real_escape_string($this->conexion, $nombres);
        $apellidos = mysqli_real_escape_string($this->conexion, $apellidos);
        $fecha_nacimiento = $fecha_nacimiento ? "'" . mysqli_real_escape_string($this->conexion, $fecha_nacimiento) . "'" : 'NULL';
        $telefono = mysqli_real_escape_string($this->conexion, $telefono);
        $email = mysqli_real_escape_string($this->conexion, $email);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        
        $sql = "INSERT INTO solicitudes_estudiantes (token, nombres, apellidos, fecha_nacimiento, telefono, email, direccion) 
                VALUES ('$token', '$nombres', '$apellidos', $fecha_nacimiento, '$telefono', '$email', '$direccion')";
        
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            return ['exito' => true, 'token' => $token, 'id' => mysqli_insert_id($this->conexion)];
        }
        
        return ['exito' => false, 'mensaje' => 'Error al crear la solicitud'];
    }
    
    /**
     * Obtener solicitud por token
     */
    public function obtenerPorToken($token) {
        if (!$this->conexion) return null;
        
        $token = mysqli_real_escape_string($this->conexion, $token);
        $sql = "SELECT * FROM solicitudes_estudiantes WHERE token = '$token' LIMIT 1";
        
        $result = mysqli_query($this->conexion, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $solicitud = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            return $solicitud;
        }
        
        return null;
    }
    
    /**
     * Marcar solicitud como usada (cuando el estudiante envía el formulario)
     */
    public function marcarComoUsado($token) {
        if (!$this->conexion) return false;
        
        $token = mysqli_real_escape_string($this->conexion, $token);
        $sql = "UPDATE solicitudes_estudiantes SET usado = 1 WHERE token = '$token'";
        
        return mysqli_query($this->conexion, $sql);
    }
    
    /**
     * Actualizar datos de la solicitud cuando el estudiante envía el formulario
     */
    public function actualizarDatos($token, $nombres, $apellidos, $fecha_nacimiento = null, $telefono = '', $email = '', $direccion = '') {
        if (!$this->conexion) return false;
        
        $token = mysqli_real_escape_string($this->conexion, $token);
        $nombres = mysqli_real_escape_string($this->conexion, $nombres);
        $apellidos = mysqli_real_escape_string($this->conexion, $apellidos);
        $fecha_nacimiento = $fecha_nacimiento ? "'" . mysqli_real_escape_string($this->conexion, $fecha_nacimiento) . "'" : 'NULL';
        $telefono = mysqli_real_escape_string($this->conexion, $telefono);
        $email = mysqli_real_escape_string($this->conexion, $email);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        
        $sql = "UPDATE solicitudes_estudiantes 
                SET nombres = '$nombres', apellidos = '$apellidos', fecha_nacimiento = $fecha_nacimiento, 
                    telefono = '$telefono', email = '$email', direccion = '$direccion', usado = 1
                WHERE token = '$token'";
        
        return mysqli_query($this->conexion, $sql);
    }
    
    /**
     * Obtener todas las solicitudes pendientes (no procesadas)
     */
    public function obtenerPendientes() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT * FROM solicitudes_estudiantes 
                WHERE usado = 1 AND procesado = 0 
                ORDER BY fecha_creacion DESC";
        
        $result = mysqli_query($this->conexion, $sql);
        $solicitudes = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $solicitudes[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $solicitudes;
    }
    
    /**
     * Marcar solicitud como procesada
     */
    public function marcarComoProcesado($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $sql = "UPDATE solicitudes_estudiantes 
                SET procesado = 1, fecha_procesado = NOW() 
                WHERE id = $id";
        
        return mysqli_query($this->conexion, $sql);
    }
    
    /**
     * Obtener solicitud por ID
     */
    public function obtenerPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "SELECT * FROM solicitudes_estudiantes WHERE id = $id LIMIT 1";
        
        $result = mysqli_query($this->conexion, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $solicitud = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            return $solicitud;
        }
        
        return null;
    }
    
    /**
     * Contar solicitudes pendientes
     */
    public function contarPendientes() {
        if (!$this->conexion) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM solicitudes_estudiantes 
                WHERE usado = 1 AND procesado = 0";
        
        $result = mysqli_query($this->conexion, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            return (int)$row['total'];
        }
        
        return 0;
    }
}

