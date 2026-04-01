<?php
require_once '../DATOS/dFinanza.php';

class nFinanza {
    
    public function insertar($datos) {
        // Compatibilidad con array o parámetros individuales
        if (is_array($datos)) {
            $fecha = $datos['fecha'] ?? '';
            $tipo_transaccion_id = $datos['tipo_transaccion_id'] ?? $datos['id_tipo_transaccion'] ?? 0;
            $concepto = $datos['concepto'] ?? '';
            $monto = $datos['monto'] ?? 0;
            $evento_id = $datos['evento_id'] ?? $datos['id_evento'] ?? null;
            $miembro_id = $datos['miembro_id'] ?? $datos['id_miembro'] ?? null;
            $referencia = $datos['referencia'] ?? null;
            $descripcion = $datos['descripcion'] ?? null;
        } else {
            $fecha = func_get_arg(0);
            $tipo_transaccion_id = func_get_arg(1);
            $concepto = func_get_arg(2);
            $monto = func_get_arg(3);
            $evento_id = func_get_arg(4) ?? null;
            $miembro_id = func_get_arg(5) ?? null;
            $referencia = func_get_arg(6) ?? null;
            $descripcion = func_get_arg(7) ?? null;
        }
        
        if (empty($concepto) || empty($monto) || empty($fecha) || empty($tipo_transaccion_id)) {
            return ['exito' => false, 'mensaje' => 'Concepto, monto, fecha y tipo de transacción son obligatorios'];
        }
        
        if (!is_numeric($monto) || $monto <= 0) {
            return ['exito' => false, 'mensaje' => 'El monto debe ser un número positivo'];
        }
        
        $dFinanza = new dFinanza();
        $resultado = $dFinanza->insertar($fecha, $tipo_transaccion_id, $concepto, $monto, $evento_id, $miembro_id, $referencia, $descripcion);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Transacción registrada exitosamente', 'id' => $resultado];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al registrar la transacción'];
        }
    }
    
    public function actualizar($datos) {
        // Compatibilidad con array o parámetros individuales
        if (is_array($datos)) {
            $id = $datos['id'] ?? 0;
            $fecha = $datos['fecha'] ?? '';
            $tipo_transaccion_id = $datos['tipo_transaccion_id'] ?? $datos['id_tipo_transaccion'] ?? 0;
            $concepto = $datos['concepto'] ?? '';
            $monto = $datos['monto'] ?? 0;
            $evento_id = $datos['evento_id'] ?? $datos['id_evento'] ?? null;
            $miembro_id = $datos['miembro_id'] ?? $datos['id_miembro'] ?? null;
            $referencia = $datos['referencia'] ?? null;
            $descripcion = $datos['descripcion'] ?? null;
            $estado = isset($datos['estado']) ? (int)$datos['estado'] : 1;
        } else {
            $id = func_get_arg(0);
            $fecha = func_get_arg(1);
            $tipo_transaccion_id = func_get_arg(2);
            $concepto = func_get_arg(3);
            $monto = func_get_arg(4);
            $evento_id = func_get_arg(5) ?? null;
            $miembro_id = func_get_arg(6) ?? null;
            $referencia = func_get_arg(7) ?? null;
            $descripcion = func_get_arg(8) ?? null;
            $estado = func_get_arg(9) ?? 1;
        }
        
        if (empty($id) || empty($concepto) || empty($monto) || empty($fecha) || empty($tipo_transaccion_id)) {
            return ['exito' => false, 'mensaje' => 'ID, concepto, monto, fecha y tipo de transacción son obligatorios'];
        }
        
        if (!is_numeric($monto) || $monto <= 0) {
            return ['exito' => false, 'mensaje' => 'El monto debe ser un número positivo'];
        }
        
        $dFinanza = new dFinanza();
        $resultado = $dFinanza->actualizar($id, $fecha, $tipo_transaccion_id, $concepto, $monto, $evento_id, $miembro_id, $referencia, $descripcion, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Transacción actualizada exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar la transacción'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de transacción requerido'];
        }
        
        $dFinanza = new dFinanza();
        $resultado = $dFinanza->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Transacción eliminada exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar la transacción'];
        }
    }
    
    public function listar() {
        $dFinanza = new dFinanza();
        return $dFinanza->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dFinanza = new dFinanza();
        return $dFinanza->buscarPorId($id);
    }
    
    public function obtenerBalanceMensual($fecha_inicio, $fecha_fin) {
        if (empty($fecha_inicio) || empty($fecha_fin)) {
            return [
                'total_ingresos' => 0,
                'total_egresos' => 0,
                'balance' => 0
            ];
        }
        
        $dFinanza = new dFinanza();
        return $dFinanza->obtenerBalanceMensual($fecha_inicio, $fecha_fin);
    }
    
    public function buscarPorConcepto($termino, $fecha_inicio = null, $fecha_fin = null) {
        if (empty($termino)) {
            return [];
        }
        
        $dFinanza = new dFinanza();
        return $dFinanza->buscarPorConcepto($termino, $fecha_inicio, $fecha_fin);
    }
    
    public function obtenerReporteTransacciones($fecha_inicio = null, $fecha_fin = null, $tipo_transaccion_id = null) {
        $dFinanza = new dFinanza();
        return $dFinanza->obtenerReporteTransacciones($fecha_inicio, $fecha_fin, $tipo_transaccion_id);
    }
}