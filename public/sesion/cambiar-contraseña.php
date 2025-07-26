<?php

require_once('../../src/auth/sesion/verificaciones-sesion.php');
validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
$error = false;
$guardado = false;


if (isset($_SESSION['error_cambioPass'])) {
    $error = true;
    $mensajeError = $_SESSION['error_cambioPass'];
    unset($_SESSION['error_cambioPass']);
}

if (isset($_SESSION['cambioPass'])) {
    $guardado = true;
    unset($_SESSION['cambioPass']);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña | Hybox</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- CDN Sweet Alert 2 -->
</head>

<body>


    <form method="post" action="../../src/auth/sesion/contraseña.php">
        <label for="contra-vieja">Contraseña Actual</label><br>
        <input type="text" name="contra-vieja"><br><br>

        <label for="contra-vieja">Nueva Contraseña</label><br>
        <input type="text" name="contra-nueva"><br><br>

        <label for="contra-vieja">Confirmar Contraseña</label><br>
        <input type="text" name="confir-contra"><br><br>

        <input type="submit" value="Cambiar Contraseña">

    </form>

    <?php if ($guardado): ?>
        <script>
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Contraseña actualizada correctamente!",
                showConfirmButton: false,
                timer: 1500
            }).then(() => { window.location.href = "../dashboard/dashboard.php"; });
        </script>
    <?php endif; ?>

    
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