<?php
/**
 * Funciones Específicas de Médicos
 */

/**
 * Subir imagen de médico
 */
function subirImagen($archivo) {
    $directorio = __DIR__ . '/../../public/assets/img/medicos/';
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

    $ruta = __DIR__ . '/../../public/assets/img/medicos/' . $nombreArchivo;

    if (file_exists($ruta)) {
        return unlink($ruta);
    }

    return false;
}

/**
 * Generar URL del perfil médico
 */
function urlPerfilMedico($medicoId) {
    // Asumiendo que SITE_URL está definido globalmente
    return SITE_URL . '/medico.php?id=' . $medicoId;
}
