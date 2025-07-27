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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth-styles.css">
</head>
<body>
    <div class="auth-container verification-container">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-cube"></i>
                <h1>Hybox</h1>
            </div>
            <div class="verification-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h2 class="verification-title">¡Vamos a verificar tu correo!</h2>
            <p class="verification-text">
                Enviaremos un correo de verificación a <span class="verification-email"><?php echo $_SESSION['correo']; ?></span>
            </p>
        </div>

        <div class="verification-actions">
            <a href="../../src/mail/veri-email.php" class="btn-verification">
                <i class="fas fa-paper-plane"></i> Enviar Correo
            </a>
            
            <p class="verification-text" style="margin-top: 16px;">
                Si ya verificaste tu correo, prueba en <a href="../../src/auth/sesion/salir.php" class="auth-link">Cerrar Sesión</a> y volver a entrar
            </p>
        </div>
    </div>
</body>
</html>