<?php
require_once '../DATOS/dAsistencia.php';

class nAsistencia {
    
    public function insertar($curso_id, $fecha, $docente_id, $observaciones = '', $detalle_estudiantes = []) {
        if (empty($curso_id) || empty($fecha) || empty($docente_id)) {
            return ['exito' => false, 'mensaje' => 'Curso, fecha y docente son obligatorios'];
        }
        
        $dAsistencia = new dAsistencia();
        $resultado = $dAsistencia->insertar($curso_id, $fecha, $docente_id, $observaciones, $detalle_estudiantes);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Asistencia registrada exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al registrar la asistencia'];
        }
    }
    
    public function actualizar($id, $curso_id, $fecha, $docente_id, $observaciones = '', $detalle_estudiantes = []) {
        if (empty($id) || empty($curso_id) || empty($fecha) || empty($docente_id)) {
            return ['exito' => false, 'mensaje' => 'ID, curso, fecha y docente son obligatorios'];
        }
        
        $dAsistencia = new dAsistencia();
        $resultado = $dAsistencia->actualizar($id, $curso_id, $fecha, $docente_id, $observaciones, $detalle_estudiantes);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Asistencia actualizada exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar la asistencia'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de asistencia requerido'];
        }
        
        $dAsistencia = new dAsistencia();
        $resultado = $dAsistencia->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Asistencia eliminada exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar la asistencia'];
        }
    }
    
    public function listar() {
        $dAsistencia = new dAsistencia();
        return $dAsistencia->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dAsistencia = new dAsistencia();
        return $dAsistencia->buscarPorId($id);
    }
    
    public function listarPorCursoYFecha($curso_id, $fecha) {
        if (empty($curso_id) || empty($fecha)) {
            return [];
        }
        
        $dAsistencia = new dAsistencia();
        return $dAsistencia->buscarPorCursoYFecha($curso_id, $fecha);
    }
    
    public function listarPorMiembroYCurso($miembro_id, $curso_id) {
        if (empty($miembro_id) || empty($curso_id)) {
            return [];
        }
        
        $dAsistencia = new dAsistencia();
        return $dAsistencia->buscarPorMiembroYCurso($miembro_id, $curso_id);
    }
    
    public function obtenerEstadisticasPorCurso($curso_id) {
        if (empty($curso_id)) {
            return [];
        }
        
        $dAsistencia = new dAsistencia();
        return $dAsistencia->obtenerEstadisticasPorCurso($curso_id);
    }
    
    // Eliminar solo un registro individual (detalle)
    public function eliminarDetalle($detalle_id) {
        if (empty($detalle_id)) {
            return ['exito' => false, 'mensaje' => 'ID de detalle requerido'];
        }
        
        $dAsistencia = new dAsistencia();
        $resultado = $dAsistencia->eliminarDetalle($detalle_id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Registro eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el registro'];
        }
    }
    
    // Insertar registro individual de asistencia
    public function insertarIndividual($asistencia_id, $estudiante_id, $estado_asistencia = 'ausente', $observacion = '') {
        if (empty($asistencia_id) || empty($estudiante_id)) {
            return ['exito' => false, 'mensaje' => 'Asistencia y estudiante son obligatorios'];
        }
        
        $dAsistencia = new dAsistencia();
        $resultado = $dAsistencia->insertarIndividual($asistencia_id, $estudiante_id, $estado_asistencia, $observacion);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Registro individual agregado exitosamente', 'id' => $resultado];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al agregar el registro individual'];
        }
    }
    
    // Buscar registros de asistencia
    public function buscar($termino, $curso_id = null, $fecha_desde = null, $fecha_hasta = null) {
        $dAsistencia = new dAsistencia();
        return $dAsistencia->buscar($termino, $curso_id, $fecha_desde, $fecha_hasta);
    }
    
    // Obtener estadísticas por estudiante
    public function obtenerEstadisticasPorEstudiante($estudiante_id, $curso_id = null) {
        if (empty($estudiante_id)) {
            return [];
        }
        
        $dAsistencia = new dAsistencia();
        return $dAsistencia->obtenerEstadisticasPorEstudiante($estudiante_id, $curso_id);
    }
    
    // Obtener estadísticas detalladas por estudiante y curso
    public function obtenerEstadisticasDetalladasPorEstudiante($estudiante_id, $curso_id) {
        if (empty($estudiante_id) || empty($curso_id)) {
            return [];
        }
        
        $dAsistencia = new dAsistencia();
        return $dAsistencia->obtenerEstadisticasDetalladasPorEstudiante($estudiante_id, $curso_id);
    }
    
    // Obtener asistencia_id por curso y fecha
    public function obtenerAsistenciaIdPorCursoYFecha($curso_id, $fecha) {
        if (empty($curso_id) || empty($fecha)) {
            return null;
        }
        
        $dAsistencia = new dAsistencia();
        return $dAsistencia->obtenerAsistenciaIdPorCursoYFecha($curso_id, $fecha);
    }
    
    // Buscar asistencias por estudiante
    public function buscarAsistenciasPorEstudiante($termino) {
        if (empty($termino)) {
            return [];
        }
        
        $dAsistencia = new dAsistencia();
        return $dAsistencia->buscarAsistenciasPorEstudiante($termino);
    }
    
    // Obtener reporte detallado por curso
    public function obtenerReporteDetalladoPorCurso($curso_id = null) {
        $dAsistencia = new dAsistencia();
        return $dAsistencia->obtenerReporteDetalladoPorCurso($curso_id);
    }
}


