<?php
include('../../../config/db.php');
require_once('../sesion/verificaciones-sesion.php');
iniSesion();
inactividad('../../../public/sesion/iniciar-sesion.php');

// Verificar que el usuario tenga sesión básica iniciada
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['correo'])) {
    header("Location: ../../../public/sesion/iniciar-sesion.php");
    exit();
}

// Verificar si el usuario tiene sesión iniciada y si se envió el código por POST
if (!isset($_SESSION['id_usuario']) || !isset($_POST['codigo'])) {
    $_SESSION['error_a2f'] = "El usuario no tiene sesión iniciada o no se envió el código.";
    header("Location: ../../../public/sesion/a2f/iniciar-a2f.php");
    exit;
}

// Obtener datos de sesion
$id_usuario = $_SESSION['id_usuario'];
$codigo_ingresado = trim($_POST['codigo']);

// Obtener el código más reciente del usuario que aún no haya expirado ni esté usado
$stmt = $conn->prepare("SELECT codigo, expiracion FROM codigos_2fa WHERE ID_USUARIO = ? AND usado = 0 ORDER BY ID_A2F DESC LIMIT 1");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No se encontró código
    $_SESSION['error_a2f'] = "No se encontró ningún código 2FA generado para ese usuario.";
    header("Location: ../../../public/sesion/a2f/iniciar-a2f.php");
    exit;
}

$row = $result->fetch_assoc();
$codigo_bd = $row['codigo'];
$expiracion = $row['expiracion'];

$hora_actual = date('Y-m-d H:i:s');

if ($codigo_ingresado == $codigo_bd && $hora_actual <= $expiracion) {
    // Código válido y no expirado

    // Actualizar codigo a usado
    $stmt = $conn->prepare("UPDATE codigos_2fa SET usado = 1 WHERE ID_USUARIO = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    // Verificar estado del a2f (Activo o inactivo)
    $stmt = $conn->prepare("SELECT a2f FROM t_usuarios WHERE ID_USUARIO = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $fila = $result->fetch_assoc();

    if ($fila['a2f'] == 0) {
        // Actualizar a2f del usuario a ACTIVO
        $stmt = $conn->prepare("UPDATE t_usuarios SET a2f = 1 WHERE ID_USUARIO = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
    }

    // Si no existe sesion activa, se habilita
    if (!isset($_SESSION['sesion'])) {
        $_SESSION['sesion'] = true;
    }

    // Redirigir a la dashboard
    header("Location: ../../../public/dashboard/dashboard.php");
    exit;

} else {
    // Código incorrecto o expirado
    $_SESSION['error_a2f'] = "El código ingresado es incorrecto o ya expiró.";
    header("Location: ../../../public/sesion/a2f/iniciar-a2f.php");
    exit;
}

?>