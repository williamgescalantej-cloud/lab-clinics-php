<?php
require_once __DIR__ . '/../../src/lib/auth.php';

// Cierra la sesión utilizando la función centralizada
logout();

// Define la URL de redirección de forma segura
// Asumiendo que SITE_URL está definido en un archivo de configuración global
if (defined('SITE_URL')) {
    header('Location: ' . SITE_URL . '/admin/login.php?status=logout');
} else {
    // Fallback por si SITE_URL no está definido
    header('Location: login.php?status=logout');
}
exit;
