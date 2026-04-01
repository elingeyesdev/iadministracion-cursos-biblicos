<?php

require_once 'dConexion.php';

class dMiembro {
    private $conexion;
    
    // Atributos de la entidad Miembro
    private $id;
    private $nombres;
    private $apellidos;
    private $fecha_nacimiento;
    private $genero;
    private $direccion;
    private $telefono;
    private $email;
    private $fecha_ingreso;
    private $estado_civil;
    private $profesion;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $nombres = null, $apellidos = null, $fecha_nacimiento = null, $genero = null, $direccion = null, $telefono = null, $email = null, $fecha_ingreso = null, $estado_civil = null, $profesion = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->nombres = $nombres;
            $this->apellidos = $apellidos;
            $this->fecha_nacimiento = $fecha_nacimiento;
            $this->genero = $genero;
            $this->direccion = $direccion;
            $this->telefono = $telefono;
            $this->email = $email;
            $this->fecha_ingreso = $fecha_ingreso;
            $this->estado_civil = $estado_civil;
            $this->profesion = $profesion;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($nombres, $apellidos, $email, $telefono, $direccion, $fecha_nacimiento, $genero, $estado_civil, $profesion = null) {
        if (!$this->conexion) return false;
        
        $nombres = mysqli_real_escape_string($this->conexion, $nombres);
        $apellidos = mysqli_real_escape_string($this->conexion, $apellidos);
        $email = mysqli_real_escape_string($this->conexion, $email);
        $telefono = mysqli_real_escape_string($this->conexion, $telefono);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        $fecha_nacimiento = $fecha_nacimiento ? "'" . mysqli_real_escape_string($this->conexion, $fecha_nacimiento) . "'" : 'NULL';
        $genero = mysqli_real_escape_string($this->conexion, $genero);
        $estado_civil = mysqli_real_escape_string($this->conexion, $estado_civil);
        $profesion = $profesion ? "'" . mysqli_real_escape_string($this->conexion, $profesion) . "'" : 'NULL';
        
        $sql = "CALL sp_miembro_insertar('$nombres', '$apellidos', $fecha_nacimiento, '$genero', '$direccion', '$telefono', '$email', '$estado_civil', $profesion)";
        $resultado = mysqli_query($this->conexion, $sql);
        
        if ($resultado) {
            $row = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
            mysqli_next_result($this->conexion);
            return $row['id'];
        }
        
        return false;
    }
    
    public function actualizar($id, $nombres, $apellidos, $email, $telefono, $direccion, $fecha_nacimiento, $genero, $estado_civil, $profesion, $estado) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $nombres = mysqli_real_escape_string($this->conexion, $nombres);
        $apellidos = mysqli_real_escape_string($this->conexion, $apellidos);
        $email = mysqli_real_escape_string($this->conexion, $email);
        $telefono = mysqli_real_escape_string($this->conexion, $telefono);
        $direccion = mysqli_real_escape_string($this->conexion, $direccion);
        $fecha_nacimiento = $fecha_nacimiento ? "'" . mysqli_real_escape_string($this->conexion, $fecha_nacimiento) . "'" : 'NULL';
        $genero = mysqli_real_escape_string($this->conexion, $genero);
        $estado_civil = mysqli_real_escape_string($this->conexion, $estado_civil);
        $profesion = $profesion ? "'" . mysqli_real_escape_string($this->conexion, $profesion) . "'" : 'NULL';
        $estado = (int)$estado;
        
        $sql = "CALL sp_miembro_actualizar($id, '$nombres', '$apellidos', $fecha_nacimiento, '$genero', '$direccion', '$telefono', '$email', '$estado_civil', $profesion, $estado)";
        $resultado = mysqli_query($this->conexion, $sql);
        mysqli_next_result($this->conexion);
        
        return $resultado;
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $sql = "CALL sp_miembro_eliminar($id)";
        $resultado = mysqli_query($this->conexion, $sql);
        mysqli_next_result($this->conexion);
        
        return $resultado;
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        $sql = "CALL sp_miembro_listar()";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $miembros = array();
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $miembros[] = $fila;
        }
        mysqli_free_result($resultado);
        mysqli_next_result($this->conexion);
        
        return $miembros;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        $sql = "CALL sp_miembro_buscar($id)";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $miembro = mysqli_fetch_assoc($resultado);
        mysqli_free_result($resultado);
        mysqli_next_result($this->conexion);
        
        return $miembro;
    }
    
    public function contarMiembrosActivos() {
        if (!$this->conexion) return 0;
        
        $sql = "CALL sp_miembro_contar_activos()";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $total = mysqli_fetch_assoc($resultado);
        mysqli_free_result($resultado);
        mysqli_next_result($this->conexion);
        
        return $total['total'];
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT id, nombres, apellidos, fecha_nacimiento, genero, direccion, telefono, email, 
                       fecha_ingreso, estado_civil, profesion, estado
                FROM miembros
                WHERE CONCAT(nombres, ' ', apellidos) LIKE '$like' AND estado = 1
                ORDER BY nombres, apellidos";
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
}