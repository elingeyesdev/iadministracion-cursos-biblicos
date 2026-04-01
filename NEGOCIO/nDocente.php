<?php
require_once '../DATOS/dDocente.php';

class nDocente {
    
    public function insertar($nombres, $apellidos, $especialidad, $telefono, $email, $direccion, $miembro_id = null, $estado = 1) {
        if (empty($nombres) || empty($apellidos)) {
            return ['exito' => false, 'mensaje' => 'Nombres y apellidos son obligatorios'];
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Email inválido'];
        }
        
        $dDocente = new dDocente();
        $resultado = $dDocente->insertar($nombres, $apellidos, $especialidad, $telefono, $email, $direccion, $miembro_id, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Docente registrado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al registrar el docente'];
        }
    }
    
    public function actualizar($id, $nombres, $apellidos, $especialidad, $telefono, $email, $direccion, $miembro_id = null, $estado = 1) {
        if (empty($id) || empty($nombres) || empty($apellidos)) {
            return ['exito' => false, 'mensaje' => 'ID, nombres y apellidos son obligatorios'];
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Email inválido'];
        }
        
        $dDocente = new dDocente();
        $resultado = $dDocente->actualizar($id, $nombres, $apellidos, $especialidad, $telefono, $email, $direccion, $miembro_id, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Docente actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el docente'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de docente requerido'];
        }
        
        $dDocente = new dDocente();
        $resultado = $dDocente->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Docente eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el docente'];
        }
    }
    
    public function listar() {
        $dDocente = new dDocente();
        return $dDocente->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dDocente = new dDocente();
        return $dDocente->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') return [];
        $dDocente = new dDocente();
        return $dDocente->buscarPorNombre($termino);
    }
    
    public function buscarDocenteConCursos($termino) {
        if (empty($termino)) {
            return [];
        }
        
        $dDocente = new dDocente();
        return $dDocente->buscarDocenteConCursos($termino);
    }
    
    public function contarDocentesActivos() {
        $dDocente = new dDocente();
        return $dDocente->contarDocentesActivos();
    }
}