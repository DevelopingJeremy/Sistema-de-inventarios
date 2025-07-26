<?php

// Subir algo en la base de datos para verificar funcionalidad
function insertRapido () {
        include('../../config/db.php');

    date_default_timezone_set('America/Costa_Rica'); // Para Costa Rica

    $ahora = date('Y-m-d H:i:s');
    // Verificar si ya existe un correo
    $stmt = $conn->prepare("INSERT INTO pruebas (tipo, fecha_hora) VALUES (?, NOW())");
    $tipo = 'Prueba_envio';
    $stmt->bind_param("s", $tipo);
    if ($stmt->execute()) {
        echo 'Enviado Correctamente';
        header('Location: codigo-email.php');
    }
}



?>