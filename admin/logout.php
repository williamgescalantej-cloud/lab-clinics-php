<?php
session_start();
session_unset();
session_destroy();

// Limpiar cookies
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Redirigir al login
header('Location: ../admin/login.php');
exit;
?>
