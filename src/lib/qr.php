<?php
/**
 * Generador de Códigos QR para LabClinics
 * Usa API externa para generar QR codes
 */

/**
 * Generar código QR para un médico
 * @param int $medicoId ID del médico
 * @param int $size Tamaño del QR en píxeles (default: 500)
 * @return string|false Nombre del archivo generado o false
 */
function generarQR($medicoId, $size = 500) {
    // URL del perfil médico
    $url = urlPerfilMedico($medicoId);
    
    // Directorio de destino
    $directorio = __DIR__ . '/../assets/qr/';
    
    // Verificar que el directorio exista
    if (!file_exists($directorio)) {
        mkdir($directorio, 0775, true);
    }
    
    // Nombre del archivo
    $nombreArchivo = 'qr_medico_' . $medicoId . '_' . time() . '.png';
    $rutaCompleta = $directorio . $nombreArchivo;
    
    // API de QR Code Generator (gratuita y confiable)
    // Opciones: L=Low, M=Medium, Q=Quartile, H=High
    $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
        'size' => $size . 'x' . $size,
        'data' => $url,
        'format' => 'png',
        'margin' => 20,
        'ecc' => 'H', // Alta corrección de errores
        'color' => '1e293b', // Color oscuro del QR
        'bgcolor' => 'ffffff' // Fondo blanco
    ]);
    
    try {
        // Descargar imagen QR con timeout
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'LabClinics/1.0'
            ]
        ]);
        
        $qrData = @file_get_contents($apiUrl, false, $context);
        
        if ($qrData === false) {
            error_log("Error descargando QR desde API para médico ID: $medicoId");
            return false;
        }
        
        // Guardar imagen
        if (file_put_contents($rutaCompleta, $qrData)) {
            // Registrar en base de datos
            registrarQR($medicoId, $nombreArchivo);
            return $nombreArchivo;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Error generando QR: " . $e->getMessage());
        return false;
    }
}

/**
 * Registrar QR en base de datos
 * @param int $medicoId
 * @param string $nombreArchivo
 * @return bool
 */
function registrarQR($medicoId, $nombreArchivo) {
    try {
        $pdo = getConnection();
        
        // Desactivar QRs anteriores
        $stmt = $pdo->prepare("UPDATE codigos_qr SET activo = 0 WHERE medico_id = ?");
        $stmt->execute([$medicoId]);
        
        // Insertar nuevo QR
        $stmt = $pdo->prepare("
            INSERT INTO codigos_qr (medico_id, ruta_archivo, activo, fecha_generacion) 
            VALUES (?, ?, 1, NOW())
        ");
        return $stmt->execute([$medicoId, $nombreArchivo]);
        
    } catch (PDOException $e) {
        error_log("Error registrando QR en BD: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener ruta del QR activo de un médico
 * @param int $medicoId
 * @return array|null Datos del QR o null
 */
function obtenerQRMedico($medicoId) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM codigos_qr 
            WHERE medico_id = ? AND activo = 1 
            ORDER BY fecha_generacion DESC 
            LIMIT 1
        ");
        $stmt->execute([$medicoId]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error obteniendo QR: " . $e->getMessage());
        return null;
    }
}

/**
 * Verificar si existe el archivo físico del QR
 * @param string $nombreArchivo
 * @return bool
 */
function existeArchivoQR($nombreArchivo) {
    $ruta = __DIR__ . '/../assets/qr/' . $nombreArchivo;
    return file_exists($ruta);
}

/**
 * Eliminar QR físico y de la BD
 * @param int $qrId ID del registro en codigos_qr
 * @return bool
 */
function eliminarQR($qrId) {
    try {
        $pdo = getConnection();
        
        // Obtener datos del QR
        $stmt = $pdo->prepare("SELECT * FROM codigos_qr WHERE id = ?");
        $stmt->execute([$qrId]);
        $qr = $stmt->fetch();
        
        if (!$qr) {
            return false;
        }
        
        // Eliminar archivo físico
        $rutaArchivo = __DIR__ . '/../assets/qr/' . $qr['ruta_archivo'];
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
        
        // Eliminar de BD
        $stmt = $pdo->prepare("DELETE FROM codigos_qr WHERE id = ?");
        return $stmt->execute([$qrId]);
        
    } catch (Exception $e) {
        error_log("Error eliminando QR: " . $e->getMessage());
        return false;
    }
}

/**
 * Generar QR para todos los médicos activos sin QR
 * @return array ['success' => int, 'errors' => int]
 */
function generarQRMasivo() {
    try {
        $pdo = getConnection();
        
        // Obtener médicos activos sin QR activo
        $stmt = $pdo->query("
            SELECT m.id 
            FROM medicos m
            LEFT JOIN codigos_qr cq ON m.id = cq.medico_id AND cq.activo = 1
            WHERE m.activo = 1 AND cq.id IS NULL
        ");
        $medicos = $stmt->fetchAll();
        
        $success = 0;
        $errors = 0;
        
        foreach ($medicos as $medico) {
            if (generarQR($medico['id'])) {
                $success++;
            } else {
                $errors++;
            }
            
            // Pequeña pausa para no saturar la API
            usleep(200000); // 0.2 segundos
        }
        
        return ['success' => $success, 'errors' => $errors];
        
    } catch (Exception $e) {
        error_log("Error en generación masiva: " . $e->getMessage());
        return ['success' => 0, 'errors' => 1];
    }
}

/**
 * Obtener estadísticas de QR codes
 * @return array
 */
function obtenerEstadisticasQR() {
    try {
        $pdo = getConnection();
        
        $stats = [];
        
        // Total de QR generados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM codigos_qr");
        $stats['total_generados'] = $stmt->fetch()['total'];
        
        // QR activos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM codigos_qr WHERE activo = 1");
        $stats['activos'] = $stmt->fetch()['total'];
        
        // Médicos sin QR
        $stmt = $pdo->query("
            SELECT COUNT(*) as total 
            FROM medicos m
            LEFT JOIN codigos_qr cq ON m.id = cq.medico_id AND cq.activo = 1
            WHERE m.activo = 1 AND cq.id IS NULL
        ");
        $stats['sin_qr'] = $stmt->fetch()['total'];
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas QR: " . $e->getMessage());
        return [
            'total_generados' => 0,
            'activos' => 0,
            'sin_qr' => 0
        ];
    }
}
?>
