<?php
require_once 'dConexion.php';

class dReporte {
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
    
    public function obtenerEstadisticasGenerales() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                (SELECT COUNT(*) FROM miembros) as total_miembros,
                (SELECT COUNT(*) FROM miembros WHERE estado = 1) as miembros_activos,
                (SELECT COUNT(*) FROM celulas) as total_celulas,
                (SELECT COUNT(*) FROM celulas WHERE estado = 1) as celulas_activas,
                (SELECT COUNT(*) FROM cursos) as total_cursos,
                (SELECT COUNT(*) FROM cursos WHERE estado = 1) as cursos_activos,
                (SELECT COUNT(*) FROM docentes) as total_docentes,
                (SELECT COUNT(*) FROM docentes WHERE estado = 1) as docentes_activos,
                (SELECT COUNT(*) FROM estudiantes) as total_estudiantes,
                (SELECT COUNT(*) FROM estudiantes WHERE estado = 1) as estudiantes_activos";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $estadisticas = array();
        
        if ($resultado) {
            $estadisticas = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
        }
        
        return $estadisticas;
    }
    
    public function obtenerCrecimientoMiembrosPorMes() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                DATE_FORMAT(fecha_ingreso, '%Y-%m') as mes,
                COUNT(*) as cantidad
                FROM miembros 
                WHERE fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(fecha_ingreso, '%Y-%m')
                ORDER BY mes";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $crecimiento = array();
        
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $crecimiento[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $crecimiento;
    }
    
    public function obtenerDistribucionCelulasPorDia() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                dia_reunion,
                COUNT(*) as cantidad
                FROM celulas 
                WHERE estado = 1
                GROUP BY dia_reunion
                ORDER BY 
                CASE dia_reunion
                    WHEN 'Lunes' THEN 1
                    WHEN 'Martes' THEN 2
                    WHEN 'Miércoles' THEN 3
                    WHEN 'Jueves' THEN 4
                    WHEN 'Viernes' THEN 5
                    WHEN 'Sábado' THEN 6
                    WHEN 'Domingo' THEN 7
                END";
        
        $result = mysqli_query($this->conexion, $sql);
        $distribucion = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $distribucion[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $distribucion;
    }
    
    public function obtenerAsistenciaPorCurso() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                c.nombre as curso,
                COUNT(a.id) as total_registros,
                SUM(CASE WHEN a.estado_asistencia = 'presente' THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN a.estado_asistencia = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                SUM(CASE WHEN a.estado_asistencia = 'retraso' THEN 1 ELSE 0 END) as retrasos
                FROM cursos c
                LEFT JOIN asistencias a ON c.id = a.curso_id AND a.estado = 1
                GROUP BY c.id, c.nombre
                ORDER BY presentes DESC";
        
        $result = mysqli_query($this->conexion, $sql);
        $asistencia = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $asistencia[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $asistencia;
    }
    
    public function obtenerFinanzasPorCelula() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                c.nombre as celula,
                SUM(CASE WHEN tt.tipo = 'ingreso' THEN f.monto ELSE 0 END) as ingresos,
                SUM(CASE WHEN tt.tipo = 'egreso' THEN f.monto ELSE 0 END) as egresos,
                SUM(CASE WHEN tt.tipo = 'ingreso' THEN f.monto ELSE -f.monto END) as balance
                FROM celulas c
                LEFT JOIN finanzas f ON c.id = f.evento_id
                LEFT JOIN tipos_transacciones tt ON f.tipo_transaccion_id = tt.id
                GROUP BY c.id, c.nombre
                ORDER BY balance DESC";
        
        $result = mysqli_query($this->conexion, $sql);
        $finanzas = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $finanzas[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $finanzas;
    }
    
    public function obtenerMiembrosSinCelula() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT m.*
                FROM miembros m
                WHERE m.id NOT IN (
                    SELECT DISTINCT miembro_id 
                    FROM celulas_miembros 
                    WHERE miembro_id IS NOT NULL
                )
                AND m.estado = 1
                ORDER BY m.nombres, m.apellidos";
        
        $result = mysqli_query($this->conexion, $sql);
        $miembros = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $miembros[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $miembros;
    }
    
    public function obtenerCumpleañosMes() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                m.nombres, 
                m.apellidos, 
                m.fecha_nacimiento,
                DATE_FORMAT(m.fecha_nacimiento, '%d') as dia,
                DATE_FORMAT(m.fecha_nacimiento, '%m') as mes
                FROM miembros m
                WHERE MONTH(m.fecha_nacimiento) = MONTH(CURDATE())
                AND m.estado = 1
                ORDER BY DAY(m.fecha_nacimiento), m.nombres";
        
        $result = mysqli_query($this->conexion, $sql);
        $cumpleaños = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $cumpleaños[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $cumpleaños;
    }
    
    public function obtenerTopDiezMiembrosAsistencia() {
        if (!$this->conexion) return [];
        
        $sql = "SELECT 
                m.nombres,
                m.apellidos,
                COUNT(a.id) as total_asistencias
                FROM miembros m
                INNER JOIN estudiantes e ON m.id = e.miembro_id
                INNER JOIN asistencias a ON e.id = a.estudiante_id
                WHERE a.estado_asistencia = 'presente' AND a.estado = 1
                AND m.estado = 1
                GROUP BY m.id, m.nombres, m.apellidos
                ORDER BY total_asistencias DESC
                LIMIT 10";
        
        $result = mysqli_query($this->conexion, $sql);
        $top_miembros = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $top_miembros[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $top_miembros;
    }
    
    public function buscarMiembrosPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        
        $sql = "SELECT m.*, 
                       (SELECT COUNT(*) FROM miembros_celulas mc WHERE mc.miembro_id = m.id) as total_celulas,
                       (SELECT COUNT(*) FROM miembros_ministerios mm WHERE mm.miembro_id = m.id) as total_ministerios
                FROM miembros m
                WHERE (m.nombres LIKE '$like' OR m.apellidos LIKE '$like' OR CONCAT(m.nombres, ' ', m.apellidos) LIKE '$like')
                AND m.estado = 1
                ORDER BY m.nombres, m.apellidos
                LIMIT 100";
        
        $result = mysqli_query($this->conexion, $sql);
        $miembros = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $miembros[] = $row;
            }
            mysqli_free_result($result);
        }
        
        return $miembros;
    }
}