<?php
// ============================================
// VALIDACIONES SEGÚN TIPO DE DATO DE LA BD
// ============================================

// 1. VALIDAR SOLO LETRAS (para nombres, categorías, etc.)
function validarSoloLetras($texto, $nombre_campo) {
    if (!preg_match("/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/", $texto)) {
        return "El campo '$nombre_campo' solo debe contener letras y espacios. No se permiten números ni caracteres especiales.";
    }
    return null;
}

// 2. VALIDAR SOLO NÚMEROS (para teléfono, ID, etc.)
function validarSoloNumeros($texto, $nombre_campo) {
    if (!preg_match("/^[0-9]+$/", $texto)) {
        return "El campo '$nombre_campo' solo debe contener números.";
    }
    return null;
}

// 3. VALIDAR NÚMERO TELÉFONO (8 dígitos exactos para Costa Rica)
function validarTelefonoCR($telefono, $nombre_campo) {
    if (!preg_match("/^[0-9]{8}$/", $telefono)) {
        return "El campo '$nombre_campo' debe tener exactamente 8 dígitos.";
    }
    return null;
}

// 4. VALIDAR FORMATO CORREO
function validarEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "El correo electrónico no es válido. Ejemplo: usuario@dominio.com";
    }
    return null;
}

// 5. VALIDAR PRECIO (número positivo con hasta 2 decimales)
function validarPrecio($precio, $nombre_campo) {
    if (!preg_match("/^[0-9]+(\.[0-9]{1,2})?$/", $precio)) {
        return "El campo '$nombre_campo' debe ser un número válido (ej: 10000 o 10000.50). No se permiten letras.";
    }
    if ($precio <= 0) {
        return "El campo '$nombre_campo' debe ser mayor a cero.";
    }
    return null;
}

// 6. VALIDAR CANTIDAD (número entero positivo)
function validarCantidad($cantidad, $nombre_campo) {
    if (!preg_match("/^[0-9]+$/", $cantidad)) {
        return "El campo '$nombre_campo' debe ser un número entero positivo. No se permiten decimales ni letras.";
    }
    if ($cantidad <= 0) {
        return "El campo '$nombre_campo' debe ser mayor a cero.";
    }
    return null;
}

// 7. VALIDAR MEDIDAS (formato: números separados por 'x')
function validarMedidas($medidas, $nombre_campo) {
    if (!empty($medidas)) {
        if (!preg_match("/^[0-9]+x[0-9]+x[0-9]+$/", $medidas)) {
            return "El campo '$nombre_campo' debe tener el formato: 45x45x90 (números separados por 'x').";
        }
    }
    return null;
}

// 8. VALIDAR MADERA (solo letras)
function validarMadera($madera, $nombre_campo) {
    if (!empty($madera)) {
        if (!preg_match("/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/", $madera)) {
            return "El campo '$nombre_campo' solo debe contener letras (ej: Roble, Cedro, Pino).";
        }
    }
    return null;
}

// 9. VALIDAR FECHA (formato YYYY-MM-DD)
function validarFecha($fecha, $nombre_campo) {
    $date = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$date || $date->format('Y-m-d') !== $fecha) {
        return "El campo '$nombre_campo' debe ser una fecha válida en formato AAAA-MM-DD.";
    }
    return null;
}

// 10. VALIDAR URL DE IMAGEN (opcional)
function validarURL($url, $nombre_campo) {
    if (!empty($url)) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return "El campo '$nombre_campo' debe ser una URL válida (ej: https://ejemplo.com/imagen.jpg).";
        }
    }
    return null;
}

// 11. VALIDAR DIRECCIÓN (letras, números, espacios, #)
function validarDireccion($direccion, $nombre_campo) {
    if (!preg_match("/^[a-zA-Z0-9áéíóúñÁÉÍÓÚÑ\s\#\-\.]+$/", $direccion)) {
        return "El campo '$nombre_campo' contiene caracteres no válidos.";
    }
    return null;
}

// 12. VALIDAR CONTRASEÑA (mínimo 6 caracteres, una letra y un número)
function validarPassword($password, $nombre_campo) {
    if (strlen($password) < 6) {
        return "El campo '$nombre_campo' debe tener al menos 6 caracteres.";
    }
    if (!preg_match("/[a-zA-Z]/", $password)) {
        return "El campo '$nombre_campo' debe contener al menos una letra.";
    }
    if (!preg_match("/[0-9]/", $password)) {
        return "El campo '$nombre_campo' debe contener al menos un número.";
    }
    return null;
}

// 13. VALIDAR REFERENCIA (solo letras y números)
function validarReferencia($referencia, $nombre_campo) {
    if (!empty($referencia)) {
        if (!preg_match("/^[a-zA-Z0-9\-]+$/", $referencia)) {
            return "El campo '$nombre_campo' solo debe contener letras, números y guiones.";
        }
    }
    return null;
}

// 14. VALIDAR QUE NO ESTÉ VACÍO
function validarRequerido($campo, $nombre_campo) {
    if (empty(trim($campo))) {
        return "El campo '$nombre_campo' es obligatorio.";
    }
    return null;
}

// 15. VALIDAR LONGITUD MÁXIMA
function validarLongitud($campo, $nombre_campo, $max) {
    if (strlen($campo) > $max) {
        return "El campo '$nombre_campo' no puede exceder los $max caracteres.";
    }
    return null;
}
?>