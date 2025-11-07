<?php
/**
 * Funciones de Validación
 */

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
