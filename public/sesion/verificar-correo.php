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
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h2 class="verification-title">¡Vamos a verificar tu correo!</h2>
            <p class="verification-text">
                Acabamos de enviar un correo de verificación a <span class="verification-email"><?php echo $_SESSION['correo']; ?></span>
            </p>
            <p class="verification-text">
                ¿No ves ningún correo? Revisa tu bandeja de SPAM o <a href="../../src/mail/veri-email.php" id="reenviar-correo" class="auth-link">reenvíalo</a>
            </p>
        </div>

        <div id="mensaje" class="message" style="display: none;"></div>

        <div class="verification-actions">
            <a href="../dashboard/dashboard.php" class="btn-verification">
                <i class="fas fa-arrow-right"></i> Continuar al Dashboard
            </a>
        </div>
    </div>

    <script>
        document.getElementById('reenviar-correo').addEventListener('click', e => {
            e.preventDefault();
            const mensajeDiv = document.getElementById('mensaje');
            mensajeDiv.style.display = 'block';
            mensajeDiv.className = 'message';
            mensajeDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando correo...';
            
            fetch('../../src/mail/veri-email.php')
            .then(response => response.text())
            .then(data => {
                mensajeDiv.className = 'message success';
                mensajeDiv.innerHTML = '<i class="fas fa-check"></i> Correo reenviado exitosamente';
            })
            .catch(err => {
                mensajeDiv.className = 'message error';
                mensajeDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al enviar el correo';
            });
        });
    </script>
</body>
</html>