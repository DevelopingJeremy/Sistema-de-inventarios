<?php

    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    iniSesion();
    $error = false;


    if (isset($_SESSION['error_login'])) {
        $error = true;
        $mensajeError = $_SESSION['error_login'];
        unset($_SESSION['error_login']);
    }

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion | Hybox</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- CDN Sweet Alert 2 -->
</head>

<body>
    <form method="post" action="../../src/auth/sesion/iniciar.php">
        <label for="correo">Correo Electrónico</label>
        <input type="email" name="correo" placeholder="Correo" required id="correo"><br>

        <label for="contraseña">Contraseña</label>
        <input type="password" name="contraseña" placeholder="Contraseña" required><br>

        <input type="submit" value="Iniciar Sesión" id="iniciarSesion">
    </form>

    <script>
        // Focus en el correo apenas entrar al sistema
        document.getElementById('correo').focus();
    </script>

    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "<?php echo $mensajeError ?>"
        });
    </script>
    <?php endif; ?>
</body>

</html>