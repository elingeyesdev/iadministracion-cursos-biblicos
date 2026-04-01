<?php

require_once 'dConexion.php';

class dUsuario {
    private $conexion;
    
    // Atributos de la entidad Usuario
    private $id;
    private $username;
    private $password;
    private $nombre_completo;
    private $rol_id;
    private $estado;
    private $fecha_creacion;
    private $fecha_actualizacion;
    
    public function __construct($id = null, $username = null, $password = null, $nombre_completo = null, $rol_id = null, $estado = 1) {
        // Inicializar conexión
        $conn = new dConexion();
        $this->conexion = $conn->Conectar();
        
        // Inicializar atributos si se proporcionan
        if ($id !== null) {
            $this->id = (int)$id;
            $this->username = $username;
            $this->password = $password;
            $this->nombre_completo = $nombre_completo;
            $this->rol_id = $rol_id ? (int)$rol_id : null;
            $this->estado = (int)$estado;
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function insertar($nombre_completo, $username, $password, $rol_id = null, $estado = 1, $email = null, $telefono = null) {
        if (!$this->conexion) return false;
        
        // password_hash() - Genera un hash seguro usando bcrypt (algoritmo por defecto)
        // PASSWORD_DEFAULT - Usa el algoritmo más seguro disponible (actualmente bcrypt)
        // El hash incluye automáticamente un salt aleatorio y es único cada vez
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Escapar datos para prevenir SQL injection
        $nombre_completo = mysqli_real_escape_string($this->conexion, $nombre_completo);
        $username = mysqli_real_escape_string($this->conexion, $username);
        $password_hash = mysqli_real_escape_string($this->conexion, $password_hashed);
        $email = $email ? mysqli_real_escape_string($this->conexion, $email) : null;
        $telefono = $telefono ? mysqli_real_escape_string($this->conexion, $telefono) : null;
        // rol_id puede ser NULL - si está vacío o es 0, se convierte a NULL
        $rol_id = ($rol_id === null || $rol_id === '' || $rol_id === 0) ? null : (int)$rol_id;
        $estado = (int)$estado;
        
        // Usar stored procedure sp_usuario_insertar
        // CALL - Ejecuta el procedimiento almacenado
        // El procedimiento recibe el hash ya generado (no genera el hash en MySQL)
        $sql = "CALL sp_usuario_insertar(?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conexion, $sql);
        
        if ($stmt) {
            // mysqli_stmt_bind_param() - Vincula los parámetros al statement preparado
            // 'sssssi' - Tipos: s=string, i=integer (username, password_hash, nombre_completo, email, telefono, estado)
            // NOTA: rol_id ya no se pasa al procedimiento, los roles se asignan usando usuario_roles
            mysqli_stmt_bind_param($stmt, 'sssssi', 
                $username, 
                $password_hash, 
                $nombre_completo, 
                $email, 
                $telefono, 
                $estado
            );
            
            // mysqli_stmt_execute() - Ejecuta el procedimiento almacenado
            $resultado = mysqli_stmt_execute($stmt);
            
            if ($resultado) {
                // mysqli_stmt_get_result() - Obtiene el resultado del procedimiento
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $id = $row['id'] ?? mysqli_insert_id($this->conexion);
                mysqli_stmt_close($stmt);
                
                // Si se proporcionó rol_id, asignarlo usando la tabla usuario_roles
                if ($rol_id !== null && $rol_id > 0) {
                    $this->asignarRoles($id, [$rol_id]);
                }
                
                return $id;
            }
            
            mysqli_stmt_close($stmt);
        } else {
            // Si falla el prepared statement, usar método directo como fallback
            $email_sql = $email ? "'" . $email . "'" : 'NULL';
            $telefono_sql = $telefono ? "'" . $telefono . "'" : 'NULL';
            $sql = "INSERT INTO usuarios (username, password, nombre_completo, email, telefono, estado) 
                    VALUES ('$username', '$password_hash', '$nombre_completo', $email_sql, $telefono_sql, $estado)";
            $resultado = mysqli_query($this->conexion, $sql);
            
            if ($resultado) {
                $id = mysqli_insert_id($this->conexion);
                // Si se proporcionó rol_id, asignarlo usando la tabla usuario_roles
                if ($rol_id !== null && $rol_id > 0) {
                    $this->asignarRoles($id, [$rol_id]);
                }
                return $id;
            }
        }
        
        return false;
    }
    
    public function actualizar($id, $nombre_completo, $username, $rol_id, $estado, $email = null, $telefono = null) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        // Escapar datos para prevenir SQL injection
        $nombre_completo = mysqli_real_escape_string($this->conexion, $nombre_completo);
        $username = mysqli_real_escape_string($this->conexion, $username);
        $email = $email ? mysqli_real_escape_string($this->conexion, $email) : null;
        $telefono = $telefono ? mysqli_real_escape_string($this->conexion, $telefono) : null;
        $rol_id = (int)$rol_id;
        $estado = (int)$estado;
        
        // Usar stored procedure sp_usuario_actualizar
        // CALL - Ejecuta el procedimiento almacenado
        $sql = "CALL sp_usuario_actualizar(?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conexion, $sql);
        
        if ($stmt) {
            // mysqli_stmt_bind_param() - Vincula los parámetros al statement preparado
            // 'issssi' - Tipos: i=integer, s=string (id, username, nombre_completo, email, telefono, estado)
            // NOTA: rol_id ya no se pasa al procedimiento, los roles se actualizan usando usuario_roles
            mysqli_stmt_bind_param($stmt, 'issssi', 
                $id, 
                $username, 
                $nombre_completo, 
                $email, 
                $telefono, 
                $estado
            );
            
            // mysqli_stmt_execute() - Ejecuta el procedimiento almacenado
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // Limpiar resultados adicionales si el procedimiento retorna múltiples result sets
            while (mysqli_more_results($this->conexion)) {
                mysqli_next_result($this->conexion);
            }
            
            return $resultado;
        } else {
            // Si falla el prepared statement, usar método directo como fallback
            $email = $email ? "'" . $email . "'" : 'NULL';
            $telefono = $telefono ? "'" . $telefono . "'" : 'NULL';
            $sql = "UPDATE usuarios 
                    SET username = '$username', nombre_completo = '$nombre_completo', 
                        email = $email, telefono = $telefono, estado = $estado 
                    WHERE id = $id";
            $resultado = mysqli_query($this->conexion, $sql);
            return $resultado;
        }
    }
    
    public function actualizarRol($id, $rol_id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        $rol_id = $rol_id ? (int)$rol_id : null;
        
        // Usar la tabla usuario_roles para asignar el rol
        if ($rol_id !== null && $rol_id > 0) {
            return $this->asignarRoles($id, [$rol_id]);
        } else {
            // Si rol_id es null, eliminar todos los roles
            return $this->asignarRoles($id, []);
        }
    }
    
    public function actualizarClave($id, $password) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        // password_hash() - Genera un hash seguro usando bcrypt (algoritmo por defecto)
        // PASSWORD_DEFAULT - Usa el algoritmo más seguro disponible (actualmente bcrypt)
        // El hash incluye automáticamente un salt aleatorio y es único cada vez
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $password_hash = mysqli_real_escape_string($this->conexion, $password_hashed);
        
        // Usar stored procedure sp_usuario_actualizar_clave
        // CALL - Ejecuta el procedimiento almacenado
        // El procedimiento recibe el hash ya generado (no genera el hash en MySQL)
        $sql = "CALL sp_usuario_actualizar_clave(?, ?)";
        $stmt = mysqli_prepare($this->conexion, $sql);
        
        if ($stmt) {
            // mysqli_stmt_bind_param() - Vincula los parámetros al statement preparado
            // 'is' - Tipos: i=integer (id), s=string (password_hash)
            mysqli_stmt_bind_param($stmt, 'is', $id, $password_hash);
            
            // mysqli_stmt_execute() - Ejecuta el procedimiento almacenado
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            return $resultado;
        } else {
            // Si falla el prepared statement, usar método directo como fallback
            $sql = "UPDATE usuarios SET password = '$password_hash' WHERE id = $id";
            $resultado = mysqli_query($this->conexion, $sql);
            return $resultado;
        }
    }
    
    public function eliminar($id) {
        if (!$this->conexion) return false;
        
        $id = (int)$id;
        
        // Usar stored procedure sp_usuario_eliminar
        // CALL - Ejecuta el procedimiento almacenado (soft delete: estado = 0)
        $sql = "CALL sp_usuario_eliminar(?)";
        $stmt = mysqli_prepare($this->conexion, $sql);
        
        if ($stmt) {
            // mysqli_stmt_bind_param() - Vincula el parámetro al statement preparado
            // 'i' - Tipo: i=integer (id)
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            // mysqli_stmt_execute() - Ejecuta el procedimiento almacenado
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // Limpiar resultados adicionales si el procedimiento retorna múltiples result sets
            while (mysqli_more_results($this->conexion)) {
                mysqli_next_result($this->conexion);
            }
            
            return $resultado;
        } else {
            // Si falla el prepared statement, usar método directo como fallback
            $sql = "UPDATE usuarios SET estado = 0 WHERE id = $id";
            $resultado = mysqli_query($this->conexion, $sql);
            return $resultado;
        }
    }
    
    public function listar() {
        if (!$this->conexion) return [];
        
        // Usar stored procedure sp_usuario_listar
        // CALL - Ejecuta el procedimiento almacenado
        $sql = "CALL sp_usuario_listar()";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $usuarios = array();
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $usuarios[] = $fila;
            }
            mysqli_free_result($resultado);
            // Limpiar resultados adicionales si el procedimiento retorna múltiples result sets
            while (mysqli_more_results($this->conexion)) {
                mysqli_next_result($this->conexion);
            }
        }
        
        return $usuarios;
    }
    
    public function buscarPorId($id) {
        if (!$this->conexion) return null;
        
        $id = (int)$id;
        
        // Usar stored procedure sp_usuario_buscar_por_id
        // CALL - Ejecuta el procedimiento almacenado
        $sql = "CALL sp_usuario_buscar_por_id(?)";
        $stmt = mysqli_prepare($this->conexion, $sql);
        
        if ($stmt) {
            // mysqli_stmt_bind_param() - Vincula el parámetro al statement preparado
            // 'i' - Tipo: i=integer (id)
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            // mysqli_stmt_execute() - Ejecuta el procedimiento almacenado
            mysqli_stmt_execute($stmt);
            
            // mysqli_stmt_get_result() - Obtiene el resultado del procedimiento
            $result = mysqli_stmt_get_result($stmt);
            $usuario = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            // Limpiar resultados adicionales si el procedimiento retorna múltiples result sets
            while (mysqli_more_results($this->conexion)) {
                mysqli_next_result($this->conexion);
            }
            
            return $usuario;
        } else {
            // Si falla el prepared statement, usar método directo como fallback
            $sql = "SELECT u.id, u.username, u.nombre_completo, u.email, u.telefono, u.estado, u.fecha_creacion
                    FROM usuarios u
                    WHERE u.id = $id";
            $resultado = mysqli_query($this->conexion, $sql);
            $usuario = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
            return $usuario;
        }
    }
    
    public function buscarPorNombre($termino) {
        if (!$this->conexion) return [];
        
        $termino = mysqli_real_escape_string($this->conexion, $termino);
        $like = "%$termino%";
        $sql = "SELECT u.id, u.username, u.nombre_completo, u.email, u.telefono, u.estado, u.fecha_creacion
                FROM usuarios u
                WHERE u.nombre_completo LIKE '$like' OR u.username LIKE '$like'
                ORDER BY u.nombre_completo";
        $resultado = mysqli_query($this->conexion, $sql);
        $usuarios = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $usuarios[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        return $usuarios;
    }
    
    public function buscar($termino = null, $rol_id = null) {
        if (!$this->conexion) return [];
        
        $condiciones = [];
        
        // Filtro por texto (nombre o username)
        if (!empty($termino)) {
            $termino = mysqli_real_escape_string($this->conexion, $termino);
            if (is_numeric($termino)) {
                // Si es numérico, buscar por ID
                $condiciones[] = "u.id = " . (int)$termino;
            } else {
                // Si es texto, buscar por nombre o username
                $like = "%$termino%";
                $condiciones[] = "(u.nombre_completo LIKE '$like' OR u.username LIKE '$like')";
            }
        }
        
        // Construir la consulta base
        $sql = "SELECT DISTINCT u.id, u.username, u.nombre_completo, u.email, u.telefono, u.estado, u.fecha_creacion
                FROM usuarios u";
        
        // Si hay filtro por rol, usar la tabla usuario_roles
        if (!empty($rol_id)) {
            $rol_id = (int)$rol_id;
            $sql .= " INNER JOIN usuario_roles ur ON u.id = ur.usuario_id";
            $condiciones[] = "ur.rol_id = $rol_id";
        }
        
        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY u.nombre_completo";
        
        $resultado = mysqli_query($this->conexion, $sql);
        $usuarios = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $usuarios[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        return $usuarios;
    }
    
    public function buscarPorUsuario($username) {
        if (!$this->conexion) return null;
        
        $username = mysqli_real_escape_string($this->conexion, $username);
        
        // Usar stored procedure sp_usuario_buscar_por_username
        // CALL - Ejecuta el procedimiento almacenado
        $sql = "CALL sp_usuario_buscar_por_username(?)";
        $stmt = mysqli_prepare($this->conexion, $sql);
        
        if ($stmt) {
            // mysqli_stmt_bind_param() - Vincula el parámetro al statement preparado
            // 's' - Tipo: s=string (username)
            mysqli_stmt_bind_param($stmt, 's', $username);
            
            // mysqli_stmt_execute() - Ejecuta el procedimiento almacenado
            mysqli_stmt_execute($stmt);
            
            // mysqli_stmt_get_result() - Obtiene el resultado del procedimiento
            // Retorna el usuario con password (hash bcrypt) para verificación con password_verify()
            $result = mysqli_stmt_get_result($stmt);
            $usuario = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            // Limpiar resultados adicionales si el procedimiento retorna múltiples result sets
            while (mysqli_more_results($this->conexion)) {
                mysqli_next_result($this->conexion);
            }
            
            return $usuario;
        } else {
            // Si falla el prepared statement, usar método directo como fallback
            $sql = "SELECT u.id, u.username, u.password, u.nombre_completo, u.email, u.telefono, u.estado, u.fecha_creacion
                    FROM usuarios u
                    WHERE u.username = '$username'";
            $resultado = mysqli_query($this->conexion, $sql);
            $usuario = mysqli_fetch_assoc($resultado);
            mysqli_free_result($resultado);
            return $usuario;
        }
    }
    
    public function obtenerPrivilegios($usuario_id) {
        if (!$this->conexion) {
            error_log("Error de conexión en obtenerPrivilegios");
            return [];
        }
        
        $usuario_id = (int)$usuario_id;
        
        // Verificar si existe la tabla usuario_roles (para múltiples roles)
        $sql_check = "SHOW TABLES LIKE 'usuario_roles'";
        $result_check = mysqli_query($this->conexion, $sql_check);
        $tiene_tabla_relacion = mysqli_num_rows($result_check) > 0;
        if ($result_check) mysqli_free_result($result_check);
        
        // Obtener privilegios de todos los roles del usuario
        $privilegios_rol = array();
        if ($tiene_tabla_relacion) {
            // Usar tabla de relación para múltiples roles
            $sql_rol = "SELECT DISTINCT p.id, p.nombre, p.descripcion, p.modulo, p.accion 
                        FROM privilegios p
                        INNER JOIN roles_privilegios rp ON p.id = rp.privilegio_id
                        INNER JOIN usuario_roles ur ON rp.rol_id = ur.rol_id
                        WHERE ur.usuario_id = $usuario_id AND p.estado = 1
                        ORDER BY modulo, accion";
        } else {
            // Si no existe la tabla usuario_roles, no hay roles asignados
            // Solo retornar privilegios personalizados
            $sql_rol = null;
        }
        
        if ($sql_rol) {
            $result_rol = mysqli_query($this->conexion, $sql_rol);
            if ($result_rol) {
                while ($fila = mysqli_fetch_assoc($result_rol)) {
                    $privilegios_rol[$fila['id']] = $fila; // Usar ID como clave para evitar duplicados
                }
                mysqli_free_result($result_rol);
            }
        }
        
        // Obtener privilegios personalizados del usuario
        $sql_personal = "SELECT DISTINCT p.id, p.nombre, p.descripcion, p.modulo, p.accion 
                         FROM privilegios p
                         INNER JOIN usuario_privilegios up ON p.id = up.privilegio_id
                         WHERE up.usuario_id = $usuario_id AND p.estado = 1
                         ORDER BY modulo, accion";
        $result_personal = mysqli_query($this->conexion, $sql_personal);
        $privilegios_personal = array();
        if ($result_personal) {
            while ($fila = mysqli_fetch_assoc($result_personal)) {
                $privilegios_personal[$fila['id']] = $fila; // Usar ID como clave para evitar duplicados
            }
            mysqli_free_result($result_personal);
        }
        
        // Combinar privilegios de todos los roles y personalizados (los personalizados tienen prioridad si hay duplicados)
        $privilegios = array_merge($privilegios_rol, $privilegios_personal);
        
        // Convertir a array indexado numéricamente
        $privilegios = array_values($privilegios);
        
        // Ordenar por módulo y acción
        usort($privilegios, function($a, $b) {
            if ($a['modulo'] == $b['modulo']) {
                return strcmp($a['accion'], $b['accion']);
            }
            return strcmp($a['modulo'], $b['modulo']);
        });
        
        // Debug: log de privilegios obtenidos
        $cantidad_roles = $tiene_tabla_relacion ? 'múltiples' : '1';
        error_log("Privilegios obtenidos para usuario $usuario_id (Roles: $cantidad_roles):");
        error_log("  - De los roles: " . count($privilegios_rol));
        error_log("  - Personalizados: " . count($privilegios_personal));
        error_log("  - Total combinado: " . count($privilegios));
        
        return $privilegios;
    }
    
    public function asignarPrivilegios($usuario_id, $privilegios_ids) {
        if (!$this->conexion) {
            error_log("Error de conexión en asignarPrivilegios");
            return false;
        }
        
        $usuario_id = (int)$usuario_id;
        
        // Eliminar privilegios personalizados actuales
        $sql_delete = "DELETE FROM usuario_privilegios WHERE usuario_id = $usuario_id";
        $result_delete = mysqli_query($this->conexion, $sql_delete);
        if (!$result_delete) {
            error_log("Error al eliminar privilegios: " . mysqli_error($this->conexion));
            return false;
        }
        
        // Insertar nuevos privilegios personalizados
        if (!empty($privilegios_ids) && is_array($privilegios_ids)) {
            foreach ($privilegios_ids as $privilegio_id) {
                $privilegio_id = (int)$privilegio_id;
                if ($privilegio_id > 0) {
                    $sql = "INSERT INTO usuario_privilegios (usuario_id, privilegio_id) 
                            VALUES ($usuario_id, $privilegio_id)
                            ON DUPLICATE KEY UPDATE usuario_id = usuario_id";
                    $result = mysqli_query($this->conexion, $sql);
                    if (!$result) {
                        error_log("Error al insertar privilegio $privilegio_id: " . mysqli_error($this->conexion));
                    }
                }
            }
        }
        
        return true;
    }
    
    public function obtenerPrivilegiosPersonalizados($usuario_id) {
        if (!$this->conexion) {
            return [];
        }
        
        $usuario_id = (int)$usuario_id;
        
        // Obtener solo privilegios personalizados del usuario (no del rol)
        $sql = "SELECT p.id, p.nombre, p.descripcion, p.modulo, p.accion 
                FROM privilegios p
                INNER JOIN usuario_privilegios up ON p.id = up.privilegio_id
                WHERE up.usuario_id = $usuario_id AND p.estado = 1
                ORDER BY modulo, accion";
        $resultado = mysqli_query($this->conexion, $sql);
        
        $privilegios = array();
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $privilegios[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $privilegios;
    }
    
    public function tienePrivilegio($usuario_id, $privilegio_nombre) {
        $privilegios = $this->obtenerPrivilegios($usuario_id);
        foreach ($privilegios as $privilegio) {
            if ($privilegio['nombre'] === $privilegio_nombre) {
                return true;
            }
        }
        return false;
    }
    
    public function asignarRol($usuario_id, $rol_id) {
        if (!$this->conexion) return false;
        
        $usuario_id = (int)$usuario_id;
        $rol_id = $rol_id ? (int)$rol_id : null;
        
        // Verificar que el usuario existe
        $usuario = $this->buscarPorId($usuario_id);
        if (!$usuario) {
            return false;
        }
        
        // Usar asignarRoles para mantener consistencia
        if ($rol_id !== null && $rol_id > 0) {
            return $this->asignarRoles($usuario_id, [$rol_id]);
        } else {
            // Si rol_id es null, eliminar todos los roles
            return $this->asignarRoles($usuario_id, []);
        }
    }
    
    public function obtenerRoles($usuario_id) {
        if (!$this->conexion) return [];
        
        $usuario_id = (int)$usuario_id;
        
        // Primero verificar si existe la tabla usuario_roles (para múltiples roles)
        $sql_check = "SHOW TABLES LIKE 'usuario_roles'";
        $result_check = mysqli_query($this->conexion, $sql_check);
        $tiene_tabla_relacion = mysqli_num_rows($result_check) > 0;
        if ($result_check) mysqli_free_result($result_check);
        
        if ($tiene_tabla_relacion) {
            // Usar tabla de relación para múltiples roles
            $sql = "SELECT r.id, r.nombre, r.descripcion 
                    FROM roles r
                    INNER JOIN usuario_roles ur ON r.id = ur.rol_id
                    WHERE ur.usuario_id = $usuario_id AND r.estado = 1
                    ORDER BY r.nombre";
        } else {
            // Si no existe la tabla usuario_roles, retornar array vacío
            // (ya no se usa el campo rol_id en usuarios)
            return [];
        }
        
        $resultado = mysqli_query($this->conexion, $sql);
        $roles = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $roles[] = $fila;
            }
            mysqli_free_result($resultado);
        }
        
        return $roles;
    }
    
    public function asignarRoles($usuario_id, $roles_ids) {
        if (!$this->conexion) return false;
        
        $usuario_id = (int)$usuario_id;
        
        // Verificar que el usuario existe
        $usuario = $this->buscarPorId($usuario_id);
        if (!$usuario) {
            return false;
        }
        
        // Verificar si existe la tabla usuario_roles
        $sql_check = "SHOW TABLES LIKE 'usuario_roles'";
        $result_check = mysqli_query($this->conexion, $sql_check);
        $tiene_tabla_relacion = mysqli_num_rows($result_check) > 0;
        if ($result_check) mysqli_free_result($result_check);
        
        if (!$tiene_tabla_relacion) {
            // Crear la tabla si no existe
            $sql_create = "CREATE TABLE IF NOT EXISTS usuario_roles (
                usuario_id INT NOT NULL,
                rol_id INT NOT NULL,
                fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (usuario_id, rol_id),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            mysqli_query($this->conexion, $sql_create);
        }
        
        // Eliminar roles actuales del usuario
        $sql_delete = "DELETE FROM usuario_roles WHERE usuario_id = $usuario_id";
        $result_delete = mysqli_query($this->conexion, $sql_delete);
        if (!$result_delete) {
            return false;
        }
        
        // Insertar nuevos roles
        if (!empty($roles_ids) && is_array($roles_ids)) {
            foreach ($roles_ids as $rol_id) {
                $rol_id = (int)$rol_id;
                if ($rol_id > 0) {
                    // Verificar que el rol existe
                    $sql_check_rol = "SELECT id FROM roles WHERE id = $rol_id AND estado = 1";
                    $result_check_rol = mysqli_query($this->conexion, $sql_check_rol);
                    if ($result_check_rol && mysqli_num_rows($result_check_rol) > 0) {
                        mysqli_free_result($result_check_rol);
                        $sql_insert = "INSERT INTO usuario_roles (usuario_id, rol_id) 
                                      VALUES ($usuario_id, $rol_id)
                                      ON DUPLICATE KEY UPDATE usuario_id = usuario_id";
                        $result_insert = mysqli_query($this->conexion, $sql_insert);
                        if (!$result_insert) {
                            error_log("Error al insertar rol $rol_id: " . mysqli_error($this->conexion));
                        }
                    } else {
                        if ($result_check_rol) mysqli_free_result($result_check_rol);
                    }
                }
            }
        }
        
        return true;
    }
}
