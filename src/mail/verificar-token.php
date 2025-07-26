<?php

    include_once('../auth/sesion/verificaciones-sesion.php');
    iniSesion();
    include('../../config/db.php');

    $token = $_GET['token'];
    $idUsuario = $_GET['id'];

    // Preparar consulta para verificar token
    $stmt = $conn->prepare("SELECT * FROM tokens WHERE token = ? AND ID_USUARIO = ? AND usado = 0");
    $stmt->bind_param("si", $token, $idUsuario);

    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        // Token válido
        $fila = $resultado->fetch_assoc();
        $_SESSION['correo_verificado'] = true;

        // Eliminar todos los tokens del usuario
        $delete = $conn->prepare("DELETE FROM tokens WHERE ID_USUARIO = ?");
        $delete->bind_param("i", $idUsuario);
        $delete->execute();

        // Actualizar el usuario como verificado
        $actualizarUsuario = $conn->prepare("UPDATE t_usuarios SET correo_verifi = 1 WHERE ID_USUARIO = ?");
        $actualizarUsuario->bind_param("i", $idUsuario);
        $actualizarUsuario->execute();

        echo "Cuenta verificada correctamente. Puede cerrar esta pestaña o volver al <a href=\"../../\">Inicio</a>";
    } else  {
        // Cuenta ya verificada o token invalido
        echo "Token Invalido o Cuenta Verificada. Volver al <a href=\"../../\">Inicio</a>";
    }

?>