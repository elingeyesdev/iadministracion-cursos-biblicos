<?php
require_once '../DATOS/dReporte.php';

class nReporte {
    
    private $dReporte;
    
    public function __construct() {
        $this->dReporte = new dReporte();
    }
    
    public function obtenerEstadisticasGenerales() {
        return $this->dReporte->obtenerEstadisticasGenerales();
    }
    
    public function obtenerCrecimientoMiembrosPorMes() {
        return $this->dReporte->obtenerCrecimientoMiembrosPorMes();
    }
    
    public function obtenerDistribucionCelulasPorDia() {
        return $this->dReporte->obtenerDistribucionCelulasPorDia();
    }
    
    public function obtenerAsistenciaPorCurso() {
        return $this->dReporte->obtenerAsistenciaPorCurso();
    }
    
    public function obtenerFinanzasPorCelula() {
        return $this->dReporte->obtenerFinanzasPorCelula();
    }
    
    public function obtenerMiembrosSinCelula() {
        return $this->dReporte->obtenerMiembrosSinCelula();
    }
    
    public function obtenerCumpleañosMes() {
        return $this->dReporte->obtenerCumpleañosMes();
    }
    
    public function obtenerTopDiezMiembrosAsistencia() {
        return $this->dReporte->obtenerTopDiezMiembrosAsistencia();
    }
    
    public function buscarMiembrosPorNombre($termino) {
        return $this->dReporte->buscarMiembrosPorNombre($termino);
    }
}

