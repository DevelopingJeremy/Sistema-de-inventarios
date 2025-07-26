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
    <p>Acabamos de enviar un correo de verificacion a <a><?php echo $_SESSION['correo']; ?></a></p>
    <p>¿No ves ningún correo? Revisa tu bandeja de SPAM o <a href="../../src/mail/veri-email.php" id="reenviar-correo">reenvíalo</a></p>
    <p id="mensaje"></p>

    Si ya verificaste tu correo, puedes <a href="../dashboard/dashboard.php">Continuar</a>

    <script>
        document.getElementById('reenviar-correo').addEventListener('click', e => {
            e.preventDefault();
            fetch('../../src/mail/veri-email.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('mensaje').innerHTML = "CORREO REENVIADO EXITOSAMENTE";
            })
            .catch(err => {
                document.getElementById('mensaje').innerHTML = "ERROR AL ENVIAR EL CORREO";
            })
        })
    </script>
</body>
</html>