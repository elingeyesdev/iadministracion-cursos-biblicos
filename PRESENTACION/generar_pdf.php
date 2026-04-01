<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar si TCPDF está disponible
$tcpdf_path = __DIR__ . '/../tcpdf/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    // Intentar con ruta alternativa de vendor
    $tcpdf_path = __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
    if (!file_exists($tcpdf_path)) {
        die('Error: TCPDF no está instalado. Por favor, coloque TCPDF en la carpeta tcpdf/ o instale con Composer: composer require tecnickcom/tcpdf');
    }
}

require_once $tcpdf_path;

$tipo = $_GET['tipo'] ?? 'general';
$busqueda = $_GET['busqueda'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$tipo_transaccion_id = $_GET['tipo_transaccion_id'] ?? '';
$curso_id_busqueda = $_GET['curso_id'] ?? ''; // Para búsqueda por curso en asistencias
$modo = $_GET['modo'] ?? 'general'; // 'general' o 'busqueda'

// Ruta del logo
$logo_path = __DIR__ . '/../imagenes/WhatsApp Image 2025-09-25 at 8.04.10 PM.jpeg';
$logo_exists = file_exists($logo_path);

// Clase personalizada para agregar marca de agua
class MYPDF extends TCPDF {
    private $logo_path;
    private $logo_exists;
    
    public function __construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $logo_path, $logo_exists) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
        $this->logo_path = $logo_path;
        $this->logo_exists = $logo_exists;
    }
    
    // Sobrescribir Header para agregar marca de agua
    public function Header() {
        // Agregar logo como marca de agua de fondo
        if ($this->logo_exists) {
            // Obtener dimensiones de la página
            $pageWidth = $this->getPageWidth();
            $pageHeight = $this->getPageHeight();
            
            // Calcular posición centrada
            $logoWidth = 120; // Ancho del logo como marca de agua (más grande)
            $logoHeight = 0; // Alto automático
            $x = ($pageWidth - $logoWidth) / 2;
            $y = ($pageHeight - 90) / 2; // Centrado verticalmente
            
            // Agregar imagen con transparencia (alpha)
            $this->SetAlpha(0.25); // Transparencia del 25% (más claro/visible)
            $this->Image($this->logo_path, $x, $y, $logoWidth, $logoHeight, '', '', '', false, 300, '', false, false, 0);
            $this->SetAlpha(1); // Restaurar opacidad completa para el contenido
        }
    }
}

// Crear PDF con clase personalizada
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, $logo_path, $logo_exists);

// Configuración del documento
$pdf->SetCreator('Sistema de Administración de Iglesia');
$pdf->SetAuthor('Iglesia Admin');
$pdf->SetTitle('Reporte - ' . ucfirst($tipo));
$pdf->SetSubject('Reporte del Sistema');

// Habilitar header para que la marca de agua aparezca en cada página
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false);

// Agregar una página
$pdf->AddPage();

// Función para agregar header con título y fecha
function agregarHeader($pdf, $titulo_reporte) {
    $pdf->SetY(10);
    
    // Título principal arriba (centrado)
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(50, 50, 150);
    $pdf->Cell(0, 10, 'REPORTE: ' . strtoupper($titulo_reporte), 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    
    // Fecha a la derecha
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetXY(150, 10);
    $pdf->Cell(45, 5, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 0, 'R');
    
    // Espacio después del header
    $pdf->SetY(25);
}

// Agregar header
agregarHeader($pdf, $tipo);

// Obtener datos según el tipo de reporte
require_once '../NEGOCIO/nReporte.php';
require_once '../NEGOCIO/nFinanza.php';
require_once '../NEGOCIO/nTipoTransaccion.php';
require_once '../NEGOCIO/nEstudiante.php';
require_once '../NEGOCIO/nDocente.php';
require_once '../NEGOCIO/nCurso.php';
require_once '../NEGOCIO/nAsistencia.php';

$nReporte = new nReporte();
$nFinanza = new nFinanza();
$nTipoTransaccion = new nTipoTransaccion();
$nEstudiante = new nEstudiante();
$nDocente = new nDocente();
$nCurso = new nCurso();
$nAsistencia = new nAsistencia();

$modo = $_GET['modo'] ?? 'general';

$data = [];
$titulo_tabla = '';

if ($tipo === 'general') {
    $data = $nReporte->obtenerEstadisticasGenerales();
    $titulo_tabla = 'Estadísticas Generales';
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 150);
    $pdf->Cell(0, 8, $titulo_tabla, 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);
    
    // Encabezado de tabla
    $pdf->SetFillColor(70, 130, 180);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(100, 7, 'Concepto', 1, 0, 'L', true);
    $pdf->Cell(80, 7, 'Cantidad', 1, 1, 'C', true);
    
    // Datos con alternancia de colores
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $fill = false;
    $items = [
        ['Total Miembros', $data['total_miembros'] ?? 0],
        ['Miembros Activos', $data['miembros_activos'] ?? 0],
        ['Células Activas', $data['celulas_activas'] ?? 0],
        ['Cursos Activos', $data['cursos_activos'] ?? 0],
        ['Docentes Activos', $data['docentes_activos'] ?? 0],
        ['Estudiantes Activos', $data['estudiantes_activos'] ?? 0],
    ];
    
    foreach ($items as $item) {
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(100, 6, $item[0], 1, 0, 'L', $fill);
        $pdf->Cell(80, 6, number_format($item[1]), 1, 1, 'R', $fill);
        $fill = !$fill;
    }
    
} elseif ($tipo === 'transacciones') {
    $data = $nFinanza->obtenerReporteTransacciones(
        $fecha_inicio ?: null,
        $fecha_fin ?: null,
        $tipo_transaccion_id ?: null
    );
    $titulo_tabla = 'Reporte de Transacciones';
    
    // Filtros aplicados
    if ($fecha_inicio || $fecha_fin) {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Período: ' . ($fecha_inicio ?: 'Inicio') . ' - ' . ($fecha_fin ?: 'Fin'), 0, 1, 'L');
        $pdf->Ln(2);
    }
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 150);
    $pdf->Cell(0, 8, $titulo_tabla, 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);
    
    if (empty($data)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'No hay transacciones para mostrar', 0, 1, 'C');
    } else {
        // Encabezados de tabla con color
        $pdf->SetFillColor(70, 130, 180);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(25, 7, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Concepto', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Tipo', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Monto', 1, 0, 'C', true);
        $pdf->Cell(45, 7, 'Miembro/Evento', 1, 1, 'C', true);
        
        // Datos con alternancia de colores
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 8);
        $total_ingresos = 0;
        $total_egresos = 0;
        $fill = false;
        
        foreach ($data as $row) {
            $fecha = date('d/m/Y', strtotime($row['fecha']));
            $concepto = substr($row['concepto'], 0, 30);
            $tipo = $row['tipo'] === 'ingreso' ? 'Ingreso' : 'Egreso';
            $monto = number_format($row['monto'], 2);
            $referencia = '';
            if ($row['miembro_nombre']) {
                $referencia = substr($row['miembro_nombre'] . ' ' . ($row['miembro_apellido'] ?? ''), 0, 25);
            } elseif ($row['evento_nombre']) {
                $referencia = substr($row['evento_nombre'], 0, 25);
            }
            
            if ($row['tipo'] === 'ingreso') {
                $total_ingresos += $row['monto'];
            } else {
                $total_egresos += $row['monto'];
            }
            
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(25, 6, $fecha, 1, 0, 'C', $fill);
            $pdf->Cell(50, 6, $concepto, 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $tipo, 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, '$' . $monto, 1, 0, 'R', $fill);
            $pdf->Cell(45, 6, $referencia, 1, 1, 'L', $fill);
            $fill = !$fill;
        }
        
        // Totales con estilo destacado
        $pdf->Ln(2);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(105, 7, 'TOTAL INGRESOS:', 1, 0, 'R', true);
        $pdf->SetTextColor(0, 128, 0);
        $pdf->Cell(40, 7, '$' . number_format($total_ingresos, 2), 1, 1, 'R', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(105, 7, 'TOTAL EGRESOS:', 1, 0, 'R', true);
        $pdf->SetTextColor(220, 20, 60);
        $pdf->Cell(40, 7, '$' . number_format($total_egresos, 2), 1, 1, 'R', true);
        
        $balance = $total_ingresos - $total_egresos;
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(105, 8, 'BALANCE:', 1, 0, 'R', true);
        $pdf->SetTextColor($balance >= 0 ? 0 : 220, $balance >= 0 ? 128 : 20, $balance >= 0 ? 0 : 60);
        $pdf->Cell(40, 8, '$' . number_format($balance, 2), 1, 1, 'R', true);
        $pdf->SetTextColor(0, 0, 0);
    }
    
} elseif ($tipo === 'busqueda' && !empty($busqueda)) {
    $data = $nFinanza->buscarPorConcepto($busqueda, $fecha_inicio ?: null, $fecha_fin ?: null);
    $titulo_tabla = 'Búsqueda: ' . $busqueda;
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 150);
    $pdf->Cell(0, 8, $titulo_tabla, 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);
    
    if (empty($data)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'No se encontraron resultados', 0, 1, 'C');
    } else {
        // Encabezados de tabla con color
        $pdf->SetFillColor(70, 130, 180);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(25, 7, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Concepto', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Tipo', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Monto', 1, 0, 'C', true);
        $pdf->Cell(45, 7, 'Referencia', 1, 1, 'C', true);
        
        // Datos con alternancia de colores
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 8);
        $fill = false;
        
        foreach ($data as $row) {
            $fecha = date('d/m/Y', strtotime($row['fecha']));
            $concepto = substr($row['concepto'], 0, 30);
            $tipo = $row['tipo'] === 'ingreso' ? 'Ingreso' : 'Egreso';
            $monto = number_format($row['monto'], 2);
            $referencia = '';
            if ($row['miembro_nombre']) {
                $referencia = substr($row['miembro_nombre'] . ' ' . ($row['miembro_apellido'] ?? ''), 0, 25);
            } elseif ($row['evento_nombre']) {
                $referencia = substr($row['evento_nombre'], 0, 25);
            }
            
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(25, 6, $fecha, 1, 0, 'C', $fill);
            $pdf->Cell(50, 6, $concepto, 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $tipo, 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, '$' . $monto, 1, 0, 'R', $fill);
            $pdf->Cell(45, 6, $referencia, 1, 1, 'L', $fill);
            $fill = !$fill;
        }
    }
    
} elseif ($tipo === 'estudiantes') {
    if ($modo === 'busqueda' && !empty($busqueda)) {
        $data = $nEstudiante->buscarEstudianteConCursos($busqueda);
    } else {
        // Para el reporte general, usar búsqueda amplia para obtener todos con cursos
        $data = $nEstudiante->buscarEstudianteConCursos('%');
    }
    
    $titulo_tabla = $modo === 'busqueda' ? 'Reporte de Estudiantes - Búsqueda: ' . $busqueda : 'Reporte General de Estudiantes';
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 150);
    $pdf->Cell(0, 8, $titulo_tabla, 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);
    
    if (empty($data)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'No hay estudiantes para mostrar', 0, 1, 'C');
    } else {
        foreach ($data as $estudiante) {
            // Título del estudiante
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(50, 50, 150);
            $pdf->Cell(0, 8, ($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? ''), 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(2);
            
            // Tabla de información del estudiante
            $pdf->SetFillColor(70, 130, 180);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(50, 7, 'Campo', 1, 0, 'C', true);
            $pdf->Cell(140, 7, 'Información', 1, 1, 'C', true);
            
            // Datos del estudiante
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 8);
            $fill = false;
            
            $campos = [
                'Email' => $estudiante['email'] ?? '-',
                'Teléfono' => $estudiante['telefono'] ?? '-',
                'Dirección' => $estudiante['direccion'] ?? '-',
                'Fecha de Nacimiento' => !empty($estudiante['fecha_nacimiento']) ? date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) : '-',
                'Estado' => isset($estudiante['estado']) && $estudiante['estado'] == 1 ? 'Activo' : 'Inactivo'
            ];
            
            foreach ($campos as $campo => $valor) {
                $pdf->SetFillColor(245, 245, 245);
                $pdf->Cell(50, 6, $campo, 1, 0, 'L', $fill);
                $pdf->Cell(140, 6, substr($valor, 0, 60), 1, 1, 'L', $fill);
                $fill = !$fill;
            }
            
            // Cursos inscritos
            if (!empty($estudiante['cursos'])) {
                $pdf->Ln(3);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetTextColor(50, 50, 150);
                $pdf->Cell(0, 7, 'Cursos Inscritos:', 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Ln(2);
                
                // Encabezado de tabla de cursos
                $pdf->SetFillColor(70, 130, 180);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(100, 7, 'Curso', 1, 0, 'C', true);
                $pdf->Cell(50, 7, 'Fecha de Inscripción', 1, 0, 'C', true);
                $pdf->Cell(40, 7, 'Estado', 1, 1, 'C', true);
                
                // Datos de cursos
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', '', 8);
                $fill = false;
                foreach ($estudiante['cursos'] as $curso) {
                    $pdf->SetFillColor(245, 245, 245);
                    $nombre_curso = substr($curso['nombre'] ?? '-', 0, 50);
                    $fecha_inscripcion = !empty($curso['fecha_inscripcion']) ? date('d/m/Y', strtotime($curso['fecha_inscripcion'])) : '-';
                    $estado_curso = isset($curso['estado']) && $curso['estado'] == 1 ? 'Activo' : 'Inactivo';
                    
                    $pdf->Cell(100, 6, $nombre_curso, 1, 0, 'L', $fill);
                    $pdf->Cell(50, 6, $fecha_inscripcion, 1, 0, 'C', $fill);
                    $pdf->Cell(40, 6, $estado_curso, 1, 1, 'C', $fill);
                    $fill = !$fill;
                }
            } else {
                $pdf->Ln(3);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(128, 128, 128);
                $pdf->Cell(0, 6, 'Sin cursos inscritos', 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
            }
            
            $pdf->Ln(8);
        }
    }
    
} elseif ($tipo === 'docentes') {
    if ($modo === 'busqueda' && !empty($busqueda)) {
        $data = $nDocente->buscarDocenteConCursos($busqueda);
    } else {
        $docentes = $nDocente->listar();
        $data = [];
        foreach ($docentes as $doc) {
            $docente_id = (int)$doc['id'];
            $cursos = $nCurso->listarPorDocente($docente_id);
            $doc['cursos'] = $cursos;
            $data[] = $doc;
        }
    }
    
    $titulo_tabla = $modo === 'busqueda' ? 'Reporte de Docentes - Búsqueda: ' . $busqueda : 'Reporte General de Docentes';
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 150);
    $pdf->Cell(0, 8, $titulo_tabla, 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);
    
    if (empty($data)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'No hay docentes para mostrar', 0, 1, 'C');
    } else {
        foreach ($data as $docente) {
            // Título del docente
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(50, 50, 150);
            $pdf->Cell(0, 8, ($docente['nombres'] ?? '') . ' ' . ($docente['apellidos'] ?? ''), 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(2);
            
            // Tabla de información del docente
            $pdf->SetFillColor(70, 130, 180);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(50, 7, 'Campo', 1, 0, 'C', true);
            $pdf->Cell(140, 7, 'Información', 1, 1, 'C', true);
            
            // Datos del docente
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 8);
            $fill = false;
            
            $campos = [
                'Especialidad' => $docente['especialidad'] ?? '-',
                'Email' => $docente['email'] ?? '-',
                'Teléfono' => $docente['telefono'] ?? '-',
                'Estado' => isset($docente['estado']) && $docente['estado'] == 1 ? 'Activo' : 'Inactivo'
            ];
            
            foreach ($campos as $campo => $valor) {
                $pdf->SetFillColor(245, 245, 245);
                $pdf->Cell(50, 6, $campo, 1, 0, 'L', $fill);
                $pdf->Cell(140, 6, substr($valor, 0, 60), 1, 1, 'L', $fill);
                $fill = !$fill;
            }
            
            // Cursos que imparte
            if (!empty($docente['cursos'])) {
                $pdf->Ln(3);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetTextColor(50, 50, 150);
                $pdf->Cell(0, 7, 'Cursos que Imparte:', 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Ln(2);
                
                // Encabezado de tabla de cursos
                $pdf->SetFillColor(70, 130, 180);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(100, 7, 'Curso', 1, 0, 'C', true);
                $pdf->Cell(50, 7, 'Fecha de Inicio', 1, 0, 'C', true);
                $pdf->Cell(40, 7, 'Estado', 1, 1, 'C', true);
                
                // Datos de cursos
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', '', 8);
                $fill = false;
                foreach ($docente['cursos'] as $curso) {
                    $pdf->SetFillColor(245, 245, 245);
                    $nombre_curso = substr($curso['nombre'] ?? '-', 0, 50);
                    $fecha_inicio = !empty($curso['fecha_inicio']) ? date('d/m/Y', strtotime($curso['fecha_inicio'])) : '-';
                    $estado_curso = isset($curso['estado']) && $curso['estado'] == 1 ? 'Activo' : 'Inactivo';
                    
                    $pdf->Cell(100, 6, $nombre_curso, 1, 0, 'L', $fill);
                    $pdf->Cell(50, 6, $fecha_inicio, 1, 0, 'C', $fill);
                    $pdf->Cell(40, 6, $estado_curso, 1, 1, 'C', $fill);
                    $fill = !$fill;
                }
            } else {
                $pdf->Ln(3);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(128, 128, 128);
                $pdf->Cell(0, 6, 'Sin cursos asignados', 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
            }
            
            $pdf->Ln(8);
        }
    }
    
} elseif ($tipo === 'cursos') {
    if ($modo === 'busqueda' && !empty($busqueda)) {
        $data = $nCurso->buscarCursoConEstudiantes($busqueda);
    } else {
        $cursos = $nCurso->listar();
        $data = [];
        foreach ($cursos as $curso) {
            $curso_id = (int)$curso['id'];
            $estudiantes = $nEstudiante->listarPorCurso($curso_id);
            $curso['estudiantes'] = $estudiantes;
            $data[] = $curso;
        }
    }
    
    $titulo_tabla = $modo === 'busqueda' ? 'Reporte de Cursos - Búsqueda: ' . $busqueda : 'Reporte General de Cursos';
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 150);
    $pdf->Cell(0, 8, $titulo_tabla, 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);
    
    if (empty($data)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'No hay cursos para mostrar', 0, 1, 'C');
    } else {
        foreach ($data as $curso) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, $curso['nombre'] ?? '', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, 'Docente: ' . (($curso['docente_nombre'] ?? '') . ' ' . ($curso['docente_apellido'] ?? 'Sin asignar')), 0, 1, 'L');
            $pdf->Cell(0, 5, 'Capacidad: ' . number_format($curso['capacidad_maxima'] ?? 0), 0, 1, 'L');
            $pdf->Cell(0, 5, 'Fecha Inicio: ' . ($curso['fecha_inicio'] ? date('d/m/Y', strtotime($curso['fecha_inicio'])) : '-'), 0, 1, 'L');
            $pdf->Cell(0, 5, 'Fecha Fin: ' . ($curso['fecha_fin'] ? date('d/m/Y', strtotime($curso['fecha_fin'])) : '-'), 0, 1, 'L');
            
            if (!empty($curso['estudiantes'])) {
                $pdf->Ln(2);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(0, 5, 'Estudiantes Inscritos (' . count($curso['estudiantes']) . '):', 0, 1, 'L');
                
                // Encabezado de tabla con color
                $pdf->SetFillColor(70, 130, 180);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell(60, 6, 'Nombre', 1, 0, 'C', true);
                $pdf->Cell(50, 6, 'Email', 1, 0, 'C', true);
                $pdf->Cell(40, 6, 'Teléfono', 1, 0, 'C', true);
                $pdf->Cell(40, 6, 'Fecha Inscripción', 1, 1, 'C', true);
                
                // Datos con alternancia de colores
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', '', 8);
                $fill = false;
                foreach ($curso['estudiantes'] as $est) {
                    $nombre = substr(($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? ''), 0, 30);
                    $email = substr($est['email'] ?? '-', 0, 25);
                    $telefono = substr($est['telefono'] ?? '-', 0, 15);
                    $fecha_inscripcion = !empty($est['fecha_inscripcion']) ? date('d/m/Y', strtotime($est['fecha_inscripcion'])) : '-';
                    
                    $pdf->SetFillColor(245, 245, 245);
                    $pdf->Cell(60, 5, $nombre, 1, 0, 'L', $fill);
                    $pdf->Cell(50, 5, $email, 1, 0, 'L', $fill);
                    $pdf->Cell(40, 5, $telefono, 1, 0, 'L', $fill);
                    $pdf->Cell(40, 5, $fecha_inscripcion, 1, 1, 'C', $fill);
                    $fill = !$fill;
                }
            }
            $pdf->Ln(5);
        }
    }
    
} elseif ($tipo === 'asistencias') {
    if (!empty($curso_id_busqueda)) {
        // Búsqueda por curso
        $data = $nAsistencia->obtenerReporteDetalladoPorCurso($curso_id_busqueda);
    } elseif ($modo === 'busqueda' && !empty($busqueda)) {
        // Búsqueda por nombre de estudiante
        $data = $nAsistencia->buscarAsistenciasPorEstudiante($busqueda);
    } else {
        // Reporte general
        $data = $nAsistencia->obtenerReporteDetalladoPorCurso();
    }
    
    $titulo_tabla = 'Reporte General de Asistencias';
    if (!empty($curso_id_busqueda)) {
        // Obtener nombre del curso
        require_once '../NEGOCIO/nCurso.php';
        $nCurso = new nCurso();
        $curso_info = $nCurso->buscarPorId($curso_id_busqueda);
        $titulo_tabla = 'Reporte de Asistencias - Curso: ' . ($curso_info['nombre'] ?? '');
    } elseif ($modo === 'busqueda' && !empty($busqueda)) {
        $titulo_tabla = 'Reporte de Asistencias - Búsqueda: ' . $busqueda;
    }
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 150);
    $pdf->Cell(0, 8, $titulo_tabla, 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);
    
    if (empty($data)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'No hay registros para mostrar', 0, 1, 'C');
    } else {
        if (!empty($curso_id_busqueda)) {
            // Reporte por curso
            foreach ($data as $curso) {
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->SetTextColor(50, 50, 150);
                $pdf->Cell(0, 8, 'Curso: ' . ($curso['curso_nombre'] ?? ''), 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
                
                $pdf->SetFont('helvetica', '', 10);
                $docente_nombre = trim(($curso['docente_nombre'] ?? '') . ' ' . ($curso['docente_apellido'] ?? ''));
                if (empty($docente_nombre)) {
                    $docente_nombre = 'Sin asignar';
                }
                $pdf->Cell(0, 6, 'Docente a cargo: ' . $docente_nombre, 0, 1, 'L');
                $pdf->Ln(2);
                
                if (!empty($curso['estudiantes'])) {
                    // Encabezado de tabla de estudiantes
                    $pdf->SetFillColor(70, 130, 180);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(50, 7, 'Estudiante', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Registros', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Asistencias', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Ausencias', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Retrasos', 1, 0, 'C', true);
                    $pdf->Cell(30, 7, '% Asistencia', 1, 1, 'C', true);
                    
                    // Datos de estudiantes
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 8);
                    $fill = false;
                    foreach ($curso['estudiantes'] as $est) {
                        $nombre = substr(($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? ''), 0, 25);
                        $total_registros = (int)($est['total_registros'] ?? 0);
                        $asistencias = (int)($est['asistencias'] ?? 0);
                        $ausencias = (int)($est['ausencias'] ?? 0);
                        $retrasos = (int)($est['retrasos'] ?? 0);
                        $porcentaje = (float)($est['porcentaje_asistencia'] ?? 0);
                        
                        $pdf->SetFillColor(245, 245, 245);
                        $pdf->Cell(50, 6, $nombre, 1, 0, 'L', $fill);
                        $pdf->Cell(20, 6, number_format($total_registros), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(0, 128, 0);
                        $pdf->Cell(20, 6, number_format($asistencias), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(220, 20, 60);
                        $pdf->Cell(20, 6, number_format($ausencias), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(255, 165, 0);
                        $pdf->Cell(20, 6, number_format($retrasos), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->Cell(30, 6, number_format($porcentaje, 2) . '%', 1, 1, 'C', $fill);
                        $pdf->SetFont('helvetica', '', 8);
                        $fill = !$fill;
                    }
                } else {
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->Cell(0, 6, 'Sin estudiantes inscritos', 0, 1, 'L');
                }
                $pdf->Ln(8);
            }
        } elseif ($modo === 'busqueda') {
            // Reporte por estudiante
            foreach ($data as $estudiante) {
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetTextColor(50, 50, 150);
                $pdf->Cell(0, 7, ($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? ''), 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(0, 5, 'Email: ' . ($estudiante['email'] ?? '-'), 0, 1, 'L');
                
                if (!empty($estudiante['asistencias'])) {
                    $pdf->Ln(2);
                    // Encabezado de tabla con color
                    $pdf->SetFillColor(70, 130, 180);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(35, 7, 'Fecha', 1, 0, 'C', true);
                    $pdf->Cell(45, 7, 'Curso', 1, 0, 'C', true);
                    $pdf->Cell(45, 7, 'Registrado por', 1, 0, 'C', true);
                    $pdf->Cell(35, 7, 'Estado', 1, 1, 'C', true);
                    
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 8);
                    $fill = false;
                    foreach ($estudiante['asistencias'] as $asist) {
                        $estado_asist = $asist['estado_asistencia'] ?? 'ausente';
                        $texto_estado = 'Ausente';
                        $color_r = 128;
                        $color_g = 128;
                        $color_b = 128;
                        
                        if ($estado_asist === 'presente') {
                            $texto_estado = 'Presente';
                            $color_r = 0;
                            $color_g = 128;
                            $color_b = 0;
                        } elseif ($estado_asist === 'retraso') {
                            $texto_estado = 'Retraso';
                            $color_r = 255;
                            $color_g = 165;
                            $color_b = 0;
                        } else {
                            $color_r = 220;
                            $color_g = 20;
                            $color_b = 60;
                        }
                        
                        $pdf->SetFillColor(245, 245, 245);
                        $pdf->Cell(35, 6, date('d/m/Y', strtotime($asist['fecha'])), 1, 0, 'C', $fill);
                        $pdf->Cell(45, 6, substr($asist['curso_nombre'] ?? '', 0, 22), 1, 0, 'L', $fill);
                        $pdf->Cell(45, 6, substr(($asist['docente_nombre'] ?? '') . ' ' . ($asist['docente_apellido'] ?? ''), 0, 22), 1, 0, 'L', $fill);
                        $pdf->SetTextColor($color_r, $color_g, $color_b);
                        $pdf->Cell(35, 6, $texto_estado, 1, 1, 'C', $fill);
                        $pdf->SetTextColor(0, 0, 0);
                        $fill = !$fill;
                    }
                }
                $pdf->Ln(5);
            }
        } else {
            // Reporte general por curso con detalles de estudiantes
            foreach ($data as $curso) {
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->SetTextColor(50, 50, 150);
                $pdf->Cell(0, 8, 'Curso: ' . ($curso['curso_nombre'] ?? ''), 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
                
                $pdf->SetFont('helvetica', '', 10);
                $docente_nombre = trim(($curso['docente_nombre'] ?? '') . ' ' . ($curso['docente_apellido'] ?? ''));
                if (empty($docente_nombre)) {
                    $docente_nombre = 'Sin asignar';
                }
                $pdf->Cell(0, 6, 'Docente a cargo: ' . $docente_nombre, 0, 1, 'L');
                $pdf->Ln(2);
                
                if (!empty($curso['estudiantes'])) {
                    // Encabezado de tabla de estudiantes
                    $pdf->SetFillColor(70, 130, 180);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(50, 7, 'Estudiante', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Registros', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Asistencias', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Ausencias', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Retrasos', 1, 0, 'C', true);
                    $pdf->Cell(30, 7, '% Asistencia', 1, 1, 'C', true);
                    
                    // Datos de estudiantes
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 8);
                    $fill = false;
                    foreach ($curso['estudiantes'] as $est) {
                        $nombre = substr(($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? ''), 0, 25);
                        $total_registros = (int)($est['total_registros'] ?? 0);
                        $asistencias = (int)($est['asistencias'] ?? 0);
                        $ausencias = (int)($est['ausencias'] ?? 0);
                        $retrasos = (int)($est['retrasos'] ?? 0);
                        $porcentaje = (float)($est['porcentaje_asistencia'] ?? 0);
                        
                        $pdf->SetFillColor(245, 245, 245);
                        $pdf->Cell(50, 6, $nombre, 1, 0, 'L', $fill);
                        $pdf->Cell(20, 6, number_format($total_registros), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(0, 128, 0);
                        $pdf->Cell(20, 6, number_format($asistencias), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(220, 20, 60);
                        $pdf->Cell(20, 6, number_format($ausencias), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(255, 165, 0);
                        $pdf->Cell(20, 6, number_format($retrasos), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->Cell(30, 6, number_format($porcentaje, 2) . '%', 1, 1, 'C', $fill);
                        $pdf->SetFont('helvetica', '', 8);
                        $fill = !$fill;
                    }
                } else {
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->Cell(0, 6, 'Sin estudiantes inscritos', 0, 1, 'L');
                }
                $pdf->Ln(8);
            }
        }
    }
    
} elseif ($tipo === 'transaccional') {
    // PDF Transaccional para asistencias
    $curso_id_busqueda = $_GET['curso_id'] ?? '';
    
    if (!empty($curso_id_busqueda)) {
        // Búsqueda por curso
        $data = $nAsistencia->obtenerReporteDetalladoPorCurso($curso_id_busqueda);
    } elseif (!empty($busqueda)) {
        // Búsqueda por nombre de estudiante
        $data = $nAsistencia->buscarAsistenciasPorEstudiante($busqueda);
    } else {
        // Reporte general
        $data = $nAsistencia->obtenerReporteDetalladoPorCurso();
    }
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 150);
    $titulo_transaccional = 'Reporte Transaccional de Asistencias';
    if (!empty($curso_id_busqueda)) {
        // Obtener nombre del curso
        require_once '../NEGOCIO/nCurso.php';
        $nCurso = new nCurso();
        $curso_info = $nCurso->buscarPorId($curso_id_busqueda);
        $titulo_transaccional .= ' - Curso: ' . ($curso_info['nombre'] ?? '');
    } elseif (!empty($busqueda)) {
        $titulo_transaccional .= ' - ' . $busqueda;
    }
    $pdf->Cell(0, 8, $titulo_transaccional, 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);
    
    if (empty($data)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'No hay registros para mostrar', 0, 1, 'C');
    } else {
        if (!empty($busqueda)) {
            // Reporte por estudiante (con búsqueda)
            foreach ($data as $estudiante) {
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetTextColor(50, 50, 150);
                $pdf->Cell(0, 7, ($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? ''), 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
                
                if (!empty($estudiante['asistencias'])) {
                    $pdf->Ln(2);
                    // Encabezado de tabla con color
                    $pdf->SetFillColor(70, 130, 180);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(35, 7, 'Fecha', 1, 0, 'C', true);
                    $pdf->Cell(45, 7, 'Curso', 1, 0, 'C', true);
                    $pdf->Cell(45, 7, 'Registrado por', 1, 0, 'C', true);
                    $pdf->Cell(35, 7, 'Estado', 1, 1, 'C', true);
                    
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 8);
                    $fill = false;
                    foreach ($estudiante['asistencias'] as $asist) {
                        $estado_asist = $asist['estado_asistencia'] ?? 'ausente';
                        $texto_estado = 'Ausente';
                        $color_r = 128;
                        $color_g = 128;
                        $color_b = 128;
                        
                        if ($estado_asist === 'presente') {
                            $texto_estado = 'Presente';
                            $color_r = 0;
                            $color_g = 128;
                            $color_b = 0;
                        } elseif ($estado_asist === 'retraso') {
                            $texto_estado = 'Retraso';
                            $color_r = 255;
                            $color_g = 165;
                            $color_b = 0;
                        } else {
                            $color_r = 220;
                            $color_g = 20;
                            $color_b = 60;
                        }
                        
                        $pdf->SetFillColor(245, 245, 245);
                        $pdf->Cell(35, 6, date('d/m/Y', strtotime($asist['fecha'])), 1, 0, 'C', $fill);
                        $pdf->Cell(45, 6, substr($asist['curso_nombre'] ?? '', 0, 22), 1, 0, 'L', $fill);
                        $pdf->Cell(45, 6, substr(($asist['docente_nombre'] ?? '') . ' ' . ($asist['docente_apellido'] ?? ''), 0, 22), 1, 0, 'L', $fill);
                        $pdf->SetTextColor($color_r, $color_g, $color_b);
                        $pdf->Cell(35, 6, $texto_estado, 1, 1, 'C', $fill);
                        $pdf->SetTextColor(0, 0, 0);
                        $fill = !$fill;
                    }
                }
                $pdf->Ln(5);
            }
        } else {
            // Reporte detallado por curso (sin búsqueda)
            foreach ($data as $curso) {
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->SetTextColor(50, 50, 150);
                $pdf->Cell(0, 8, 'Curso: ' . ($curso['curso_nombre'] ?? ''), 0, 1, 'L');
                $pdf->SetTextColor(0, 0, 0);
                
                $pdf->SetFont('helvetica', '', 10);
                $docente_nombre = trim(($curso['docente_nombre'] ?? '') . ' ' . ($curso['docente_apellido'] ?? ''));
                if (empty($docente_nombre)) {
                    $docente_nombre = 'Sin asignar';
                }
                $pdf->Cell(0, 6, 'Docente a cargo: ' . $docente_nombre, 0, 1, 'L');
                $pdf->Ln(2);
                
                if (!empty($curso['estudiantes'])) {
                    // Encabezado de tabla de estudiantes
                    $pdf->SetFillColor(70, 130, 180);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(50, 7, 'Estudiante', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Registros', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Asistencias', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Ausencias', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, 'Retrasos', 1, 0, 'C', true);
                    $pdf->Cell(30, 7, '% Asistencia', 1, 1, 'C', true);
                    
                    // Datos de estudiantes
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 8);
                    $fill = false;
                    foreach ($curso['estudiantes'] as $est) {
                        $nombre = substr(($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? ''), 0, 25);
                        $total_registros = (int)($est['total_registros'] ?? 0);
                        $asistencias = (int)($est['asistencias'] ?? 0);
                        $ausencias = (int)($est['ausencias'] ?? 0);
                        $retrasos = (int)($est['retrasos'] ?? 0);
                        $porcentaje = (float)($est['porcentaje_asistencia'] ?? 0);
                        
                        $pdf->SetFillColor(245, 245, 245);
                        $pdf->Cell(50, 6, $nombre, 1, 0, 'L', $fill);
                        $pdf->Cell(20, 6, number_format($total_registros), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(0, 128, 0);
                        $pdf->Cell(20, 6, number_format($asistencias), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(220, 20, 60);
                        $pdf->Cell(20, 6, number_format($ausencias), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(255, 165, 0);
                        $pdf->Cell(20, 6, number_format($retrasos), 1, 0, 'C', $fill);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->Cell(30, 6, number_format($porcentaje, 2) . '%', 1, 1, 'C', $fill);
                        $pdf->SetFont('helvetica', '', 8);
                        $fill = !$fill;
                    }
                } else {
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->Cell(0, 6, 'Sin estudiantes inscritos', 0, 1, 'L');
                }
                $pdf->Ln(8);
            }
        }
    }
}

// Generar PDF
$nombre_archivo = 'reporte_' . $tipo . '_' . date('Ymd_His') . '.pdf';
$pdf->Output($nombre_archivo, 'I'); // 'I' para mostrar en navegador, 'D' para descargar

