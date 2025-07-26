<?php

    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    iniSesion();
    validarSesion('../sesion/iniciar-sesion.php');
    inactividad('../sesion/iniciar-sesion.php');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Email | Hybox</title>
</head>
<body>
    <h2>Vamos a verificar tu correo!</h2>
    <p>Enviaremos un correo de verificacion a <a><?php echo $_SESSION['correo']; ?></a></p>
    <a href="../../src/mail/veri-email.php">Enviar Correo</a>
    <p>Si ya verificaste tu correo, prueba en <a href="../../src/auth/sesion/salir.php">Cerrar Sesión</a> y volver a entrar</p>
</body>
</html>