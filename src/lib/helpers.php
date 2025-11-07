<?php
/**
 * Funciones de Ayuda Generales
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
