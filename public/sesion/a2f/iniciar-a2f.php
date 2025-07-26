<?php

    include('../../../config/db.php');
    require_once('../../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../../sesion/iniciar-sesion.php', '../../sesion/envio-correo.php', '../../empresa/registrar-empresa.php');

    if (isset($_SESSION['error_a2f'])) {
        $mensajeError = $_SESSION['error_a2f'];
        echo $mensajeError;
    }

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A2F</title>
</head>
<body>
    <h2>Autenticación en 2 Factores</h2>
    <p>Te acabamos de enviar un codigo de 4 digitos a tu correo <a href=""><?php echo $_SESSION['correo']; ?></a></p>
    <form action="../../../src/auth/a2f/procesar-a2f.php" method="post">
        <label for="codigo">Codigo</label>
        <input type="text" name="codigo" maxlength="4" required><br><br>
        <input type="submit" value="Verificar"><br>
    </form>

    <p>¿No ves tu código? Revisa la carpeta de SPAM o <a href="../../../src/auth/sesion/enviar-a2f.php" id="reenviar-a2f">reenvíalo</a></p>

    <p id="mensaje"></p>
    
    <script>
        document.getElementById('reenviar-a2f').addEventListener('click', e => {
            e.preventDefault();
            fetch('../../../src/auth/sesion/enviar-a2f.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('mensaje').innerHTML = "CODIGO REENVIADO EXITOSAMENTE";
            })
            .catch(err => {
                document.getElementById('mensaje').innerHTML = "ERROR AL ENVIAR EL CORREO";
            })
        })
    </script>
</body>
</html>