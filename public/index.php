<?php
// Redirige de forma segura a la página de inicio de sesión del administrador
// usando una ruta relativa al directorio actual.
header('Location: admin/login.php');
exit;
