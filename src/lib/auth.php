<?php
/**
 * Funciones de Autenticación
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verificar si el usuario está logueado
 */
function isLogged() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
}

/**
 * Obtener ID del usuario actual
 */
function getUserId() {
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Obtener nombre del usuario actual
 */
function getUserName() {
    return $_SESSION['admin_nombre'] ?? null;
}

/**
 * Verificar credenciales de login
 */
function verificarLogin($email, $password) {
    // La conexión a la BBDD se asume que ya está incluida,
    // pero getConnection() la manejará de forma segura.
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            // Actualizar última sesión
            $update = $pdo->prepare("UPDATE usuarios SET ultima_sesion = NOW() WHERE id = ?");
            $update->execute([$usuario['id']]);
            
            return $usuario;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Error en verificarLogin: " . $e->getMessage());
        return false;
    }
}

/**
 * Iniciar sesión de administrador
 */
function loginAdmin($usuario) {
    $_SESSION['admin_id'] = $usuario['id'];
    $_SESSION['admin_email'] = $usuario['email'];
    $_SESSION['admin_nombre'] = $usuario['nombre'];
    $_SESSION['admin_login_time'] = time();
}

/**
 * Cerrar sesión
 */
function logout() {
    session_unset();
    session_destroy();
}

/**
 * Proteger página - redirige a login si no está autenticado
 */
function requireLogin() {
    if (!isLogged()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Redirigir si ya está logueado
 */
function redirectIfLogged() {
    if (isLogged()) {
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit;
    }
}
?>
