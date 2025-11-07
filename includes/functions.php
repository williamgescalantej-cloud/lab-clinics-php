<?php
/**
 * Funciones Generales
 */

/**
 * Sanitizar entrada de texto
 */
function sanitizar($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirigir a una URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Mostrar mensaje de alerta
 */
function mostrarAlerta($mensaje, $tipo = 'info') {
    return '<div class="alert alert-' . $tipo . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($mensaje) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

/**
 * Subir imagen de médico
 */
function subirImagen($archivo) {
    $directorio = __DIR__ . '/../assets/img/medicos/';
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
    $tamañoMaximo = 5 * 1024 * 1024; // 5MB
    
    if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($archivo['size'] > $tamañoMaximo) {
        return false;
    }
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensionesPermitidas)) {
        return false;
    }
    
    $nombreArchivo = uniqid('medico_') . '.' . $extension;
    $rutaDestino = $directorio . $nombreArchivo;
    
    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        return $nombreArchivo;
    }
    
    return false;
}

/**
 * Eliminar imagen de médico
 */
function eliminarImagen($nombreArchivo) {
    if (empty($nombreArchivo)) {
        return false;
    }
    
    $ruta = __DIR__ . '/../assets/img/medicos/' . $nombreArchivo;
    
    if (file_exists($ruta)) {
        return unlink($ruta);
    }
    
    return false;
}

/**
 * Formatear fecha para mostrar
 */
function formatearFecha($fecha) {
    $timestamp = strtotime($fecha);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Obtener IP del visitante
 */
function obtenerIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Obtener User Agent
 */
function obtenerUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
}

/**
 * Generar URL del perfil médico
 */
function urlPerfilMedico($medicoId) {
    return SITE_URL . '/public/medico.php?id=' . $medicoId;
}

/**
 * Validar email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar teléfono (formato boliviano)
 */
function validarTelefono($telefono) {
    $patron = '/^(\+?591)?[-\s]?\d{7,8}$/';
    return preg_match($patron, $telefono);
}
?>
