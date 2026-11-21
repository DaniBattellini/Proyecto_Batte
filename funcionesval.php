<?php
function ValidacionDatos() {
    global $error;
    $var_bool = true;

    // Validar nombre y apellido (solo letras y espacios, permite tildes y ñ)
    if (!preg_match("/^[a-zA-ZáéíóúüÁÉÍÓÚÜñÑ ]+$/u", $_POST['nombre'])) {
        $error .= "Error nombre ";
        $var_bool = false;
    }
    if (!preg_match("/^[a-zA-ZáéíóúüÁÉÍÓÚÜñÑ ]+$/u", $_POST['apellido'])) {
        $error .= "Error apellido ";
        $var_bool = false;
    }

    // Validar DNI (7 u 8 dígitos numéricos)
    if (!preg_match("/^\d{7,8}$/", $_POST['dni'])) {
        $error .= "Error DNI ";
        $var_bool = false;
    }

    // Validar CUIL (11 dígitos numéricos)
    if (!preg_match("/^\d{11}$/", $_POST['cuil'])) {
        $error .= "Error CUIL ";
        $var_bool = false;
    }

    return $var_bool;
}
?>
