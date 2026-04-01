<?php
require_once '../DATOS/dCurso.php';

class nCurso {
    
    public function insertar($nombre, $descripcion, $fecha_inicio, $fecha_fin, $capacidad_maxima, $docente_id, $estado = 1) {
        if (empty($nombre) || empty($docente_id) || empty($fecha_inicio) || empty($fecha_fin)) {
            return ['exito' => false, 'mensaje' => 'Nombre, docente y fechas son obligatorios'];
        }
        
        $dCurso = new dCurso();
        $resultado = $dCurso->insertar($nombre, $descripcion, $fecha_inicio, $fecha_fin, $capacidad_maxima, $docente_id, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Curso registrado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al registrar el curso'];
        }
    }
    
    public function actualizar($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $capacidad_maxima, $docente_id, $estado = 1) {
        if (empty($id) || empty($nombre) || empty($docente_id) || empty($fecha_inicio) || empty($fecha_fin)) {
            return ['exito' => false, 'mensaje' => 'ID, nombre, docente y fechas son obligatorios'];
        }
        
        $dCurso = new dCurso();
        $resultado = $dCurso->actualizar($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $capacidad_maxima, $docente_id, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Curso actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el curso'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de curso requerido'];
        }
        
        $dCurso = new dCurso();
        $resultado = $dCurso->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Curso eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el curso'];
        }
    }
    
    public function listar() {
        $dCurso = new dCurso();
        return $dCurso->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dCurso = new dCurso();
        return $dCurso->buscarPorId($id);
    }
    
    public function listarPorDocente($docente_id) {
        if (empty($docente_id)) {
            return [];
        }
        
        $dCurso = new dCurso();
        return $dCurso->listarPorDocente($docente_id);
    }
    
    public function listarActivos() {
        $dCurso = new dCurso();
        return $dCurso->listarActivos();
    }
    
    public function buscarPorId($id) {
        if (empty($id)) return null;
        $dCurso = new dCurso();
        return $dCurso->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') return [];
        $dCurso = new dCurso();
        return $dCurso->buscarPorNombre($termino);
    }
    
    public function buscarCursoConEstudiantes($termino) {
        if (empty($termino)) {
            return [];
        }
        
        $dCurso = new dCurso();
        return $dCurso->buscarCursoConEstudiantes($termino);
    }
    
    public function contarEstudiantesPorCurso($curso_id) {
        if (empty($curso_id)) {
            return 0;
        }
        
        $dCurso = new dCurso();
        return $dCurso->contarEstudiantesPorCurso($curso_id);
    }
    
    public function obtenerEstadisticasGenerales() {
        $dCurso = new dCurso();
        return $dCurso->obtenerEstadisticasGenerales();
    }
}


