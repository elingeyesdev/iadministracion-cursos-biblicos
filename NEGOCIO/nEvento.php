<?php
require_once '../DATOS/dEvento.php';

class nEvento {
    
    public function insertar($datos) {
        // Compatibilidad con array o parámetros individuales
        if (is_array($datos)) {
            $nombre = $datos['nombre'] ?? $datos['titulo'] ?? '';
            $descripcion = $datos['descripcion'] ?? '';
            $fecha_inicio = $datos['fecha_inicio'] ?? $datos['fecha'] ?? '';
            $fecha_fin = $datos['fecha_fin'] ?? null;
            $tipo_evento_id = $datos['tipo_evento_id'] ?? $datos['id_tipo_evento'] ?? 0;
            $es_economico = isset($datos['es_economico']) ? (int)$datos['es_economico'] : 0;
            $presupuesto = isset($datos['presupuesto']) ? (float)$datos['presupuesto'] : 0;
        } else {
            $nombre = func_get_arg(0);
            $descripcion = func_get_arg(1);
            $fecha_inicio = func_get_arg(2);
            $fecha_fin = func_get_arg(3) ?? null;
            $tipo_evento_id = func_get_arg(4);
            $es_economico = func_get_arg(5) ?? 0;
            $presupuesto = func_get_arg(6) ?? 0;
        }
        
        if (empty($nombre) || empty($fecha_inicio) || empty($tipo_evento_id)) {
            return ['exito' => false, 'mensaje' => 'Nombre, fecha de inicio y tipo de evento son obligatorios'];
        }
        
        if (strtotime($fecha_inicio) < strtotime(date('Y-m-d'))) {
            return ['exito' => false, 'mensaje' => 'La fecha de inicio del evento no puede ser anterior a hoy'];
        }
        
        $dEvento = new dEvento();
        $resultado = $dEvento->insertar($nombre, $descripcion, $fecha_inicio, $fecha_fin, $tipo_evento_id, $es_economico, $presupuesto);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Evento creado exitosamente', 'id' => $resultado];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al crear el evento'];
        }
    }
    
    public function actualizar($datos) {
        // Compatibilidad con array o parámetros individuales
        if (is_array($datos)) {
            $id = $datos['id'] ?? 0;
            $nombre = $datos['nombre'] ?? $datos['titulo'] ?? '';
            $descripcion = $datos['descripcion'] ?? '';
            $fecha_inicio = $datos['fecha_inicio'] ?? $datos['fecha'] ?? '';
            $fecha_fin = $datos['fecha_fin'] ?? null;
            $tipo_evento_id = $datos['tipo_evento_id'] ?? $datos['id_tipo_evento'] ?? 0;
            $es_economico = isset($datos['es_economico']) ? (int)$datos['es_economico'] : 0;
            $presupuesto = isset($datos['presupuesto']) ? (float)$datos['presupuesto'] : 0;
        } else {
            $id = func_get_arg(0);
            $nombre = func_get_arg(1);
            $descripcion = func_get_arg(2);
            $fecha_inicio = func_get_arg(3);
            $fecha_fin = func_get_arg(4) ?? null;
            $tipo_evento_id = func_get_arg(5);
            $es_economico = func_get_arg(6) ?? 0;
            $presupuesto = func_get_arg(7) ?? 0;
        }
        
        if (empty($id) || empty($nombre) || empty($fecha_inicio) || empty($tipo_evento_id)) {
            return ['exito' => false, 'mensaje' => 'ID, nombre, fecha de inicio y tipo de evento son obligatorios'];
        }
        
        if (strtotime($fecha_inicio) < strtotime(date('Y-m-d'))) {
            return ['exito' => false, 'mensaje' => 'La fecha de inicio del evento no puede ser anterior a hoy'];
        }
        
        $dEvento = new dEvento();
        $resultado = $dEvento->actualizar($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $tipo_evento_id, $es_economico, $presupuesto);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Evento actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el evento'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de evento requerido'];
        }
        
        $dEvento = new dEvento();
        $resultado = $dEvento->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Evento eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el evento'];
        }
    }
    
    public function listar() {
        $dEvento = new dEvento();
        return $dEvento->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dEvento = new dEvento();
        return $dEvento->buscarPorId($id);
    }
    
    public function obtenerPorFecha($fecha) {
        if (empty($fecha)) {
            return [];
        }
        
        $dEvento = new dEvento();
        return $dEvento->buscarPorFecha($fecha);
    }
    
    public function buscarPorId($id) {
        if (empty($id)) return null;
        $dEvento = new dEvento();
        return $dEvento->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') return [];
        $dEvento = new dEvento();
        return $dEvento->buscarPorNombre($termino);
    }
    
    public function contarEventosActivos() {
        $dEvento = new dEvento();
        return $dEvento->contarEventosActivos();
    }
}