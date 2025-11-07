<?php
/**
 * LabClinics Connect - Configuración de Base de Datos
 * Sistema de Gestión de Referencias Médicas
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'labclinics');
define('DB_USER', 'root');
define('DB_PASS', ''); // Cambia si tienes contraseña en XAMPP/LAMPP
define('DB_CHARSET', 'utf8mb4');

// Configuración general del sitio
define('SITE_URL', 'http://localhost/proyectos/labclinics/public');
define('SITE_NAME', 'LabClinics Connect');
define('SITE_SHORT_NAME', 'LabClinics');
define('SITE_VERSION', '1.0.0');

// Información del laboratorio
define('LAB_NAME', 'LabClinics');
define('LAB_PHONE', '+591 (2) 2345-6789');
define('LAB_EMAIL', 'contacto@labclinics.com.bo');
define('LAB_ADDRESS', 'Av. 6 de Agosto #2055, La Paz, Bolivia');
define('LAB_WEBSITE', 'www.labclinics.com.bo');

/**
 * Función para obtener conexión PDO
 * @return PDO
 */
function getConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos. Por favor, contacte al administrador del sistema.");
        }
    }
    
    return $pdo;
}
?>
