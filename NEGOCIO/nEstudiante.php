<?php

require_once '../DATOS/dEstudiante.php';

class nEstudiante {
    
    public function insertar($miembro_id, $curso_id) {
        if (empty($miembro_id) || empty($curso_id)) {
            return ['exito' => false, 'mensaje' => 'Miembro y curso son obligatorios'];
        }
        
        $dEstudiante = new dEstudiante();
        
        $existe = $this->verificarInscripcionExistente($miembro_id, $curso_id);
        if ($existe) {
            return ['exito' => false, 'mensaje' => 'El miembro ya está inscrito en este curso'];
        }
        
        $resultado = $dEstudiante->insertar($miembro_id, $curso_id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Estudiante inscrito exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al inscribir al estudiante'];
        }
    }
    
    public function actualizar($id, $miembro_id, $curso_id, $estado) {
        if (empty($id) || empty($miembro_id) || empty($curso_id) || empty($estado)) {
            return ['exito' => false, 'mensaje' => 'Todos los campos son obligatorios'];
        }
        
        if (!in_array($estado, ['activo', 'inactivo', 'graduado'])) {
            return ['exito' => false, 'mensaje' => 'El estado debe ser activo, inactivo o graduado'];
        }
        
        $dEstudiante = new dEstudiante();
        $resultado = $dEstudiante->actualizar($id, $miembro_id, $curso_id, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Estudiante actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el estudiante'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de estudiante requerido'];
        }
        
        $dEstudiante = new dEstudiante();
        $resultado = $dEstudiante->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Estudiante eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el estudiante'];
        }
    }
    
    public function listar() {
        $dEstudiante = new dEstudiante();
        return $dEstudiante->listar();
    }
    
    public function buscarPorNombre($termino) {
        if (empty($termino)) {
            return [];
        }
        
        $dEstudiante = new dEstudiante();
        return $dEstudiante->buscarPorNombre($termino);
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dEstudiante = new dEstudiante();
        return $dEstudiante->buscarPorId($id);
    }
    
    public function listarPorCurso($curso_id) {
        if (empty($curso_id)) {
            return [];
        }
        
        $dEstudiante = new dEstudiante();
        return $dEstudiante->buscarPorCurso($curso_id);
    }
    
    public function contarEstudiantesActivos() {
        $dEstudiante = new dEstudiante();
        return $dEstudiante->contarEstudiantesActivos();
    }
    
    public function contarEstudiantesPorCurso($curso_id) {
        $dEstudiante = new dEstudiante();
        return $dEstudiante->contarEstudiantesPorCurso($curso_id);
    }
    
    private function verificarInscripcionExistente($miembro_id, $curso_id) {
        $dEstudiante = new dEstudiante();
        $estudiantes = $dEstudiante->buscarPorCurso($curso_id);
        
        foreach ($estudiantes as $estudiante) {
            if ($estudiante['miembro_id'] == $miembro_id) {
                return true;
            }
        }
        return false;
    }
    
    // Crear estudiante desde cero
    public function crearEstudiante($nombres, $apellidos, $fecha_nacimiento = null, $telefono = '', $email = '', $direccion = '', $miembro_id = null, $estado = 1) {
        if (empty($nombres) || empty($apellidos)) {
            return ['exito' => false, 'mensaje' => 'Nombres y apellidos son obligatorios'];
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Email inválido'];
        }
        
        $dEstudiante = new dEstudiante();
        $resultado = $dEstudiante->crearEstudiante($nombres, $apellidos, $fecha_nacimiento, $telefono, $email, $direccion, $miembro_id, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Estudiante creado exitosamente', 'id' => $resultado];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al crear el estudiante'];
        }
    }
    
    // Obtener estudiante por ID (solo datos del estudiante)
    public function obtenerEstudiantePorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dEstudiante = new dEstudiante();
        return $dEstudiante->obtenerEstudiantePorId($id);
    }
    
    // Actualizar datos del estudiante
    public function actualizarEstudiante($id, $nombres, $apellidos, $fecha_nacimiento = null, $telefono = '', $email = '', $direccion = '', $miembro_id = null, $estado = 1) {
        if (empty($id) || empty($nombres) || empty($apellidos)) {
            return ['exito' => false, 'mensaje' => 'ID, nombres y apellidos son obligatorios'];
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Email inválido'];
        }
        
        $dEstudiante = new dEstudiante();
        $resultado = $dEstudiante->actualizarEstudiante($id, $nombres, $apellidos, $fecha_nacimiento, $telefono, $email, $direccion, $miembro_id, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Estudiante actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el estudiante'];
        }
    }
    
    // Inscribir estudiante en curso
    public function inscribirEnCurso($estudiante_id, $curso_id, $fecha_inscripcion = null) {
        if (empty($estudiante_id) || empty($curso_id)) {
            return ['exito' => false, 'mensaje' => 'Estudiante y curso son obligatorios'];
        }
        
        $dEstudiante = new dEstudiante();
        $resultado = $dEstudiante->inscribirEnCurso($estudiante_id, $curso_id, $fecha_inscripcion);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Estudiante inscrito en el curso exitosamente', 'id' => $resultado];
        } else {
            // Verificar el último error de MySQL
            $conn = new dConexion();
            $conexion = $conn->Conectar();
            $error = mysqli_error($conexion);
            mysqli_close($conexion);
            
            if (!empty($error)) {
                return ['exito' => false, 'mensaje' => 'Error al inscribir al estudiante: ' . $error];
            } else {
                return ['exito' => false, 'mensaje' => 'Error al inscribir al estudiante. Puede que ya esté inscrito en este curso o que el estudiante/curso no exista.'];
            }
        }
    }
    
    // Obtener datos de miembro para copiar
    public function obtenerDatosMiembro($miembro_id) {
        if (empty($miembro_id)) {
            return null;
        }
        
        $dEstudiante = new dEstudiante();
        return $dEstudiante->obtenerDatosMiembro($miembro_id);
    }
    
    // Buscar estudiante con sus cursos
    public function buscarEstudianteConCursos($termino) {
        if (empty($termino)) {
            return [];
        }
        
        $dEstudiante = new dEstudiante();
        return $dEstudiante->buscarEstudianteConCursos($termino);
    }
    
    // Obtener cursos en los que está inscrito un estudiante
    public function obtenerCursosPorEstudiante($estudiante_id) {
        if (empty($estudiante_id)) {
            return [];
        }
        
        $dEstudiante = new dEstudiante();
        return $dEstudiante->obtenerCursosPorEstudiante($estudiante_id);
    }
}