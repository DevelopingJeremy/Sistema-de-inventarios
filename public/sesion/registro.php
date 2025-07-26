<?php

    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    iniSesion();
    $error = false;

    if (isset($_SESSION['error_registro'])) {
        $error = true;
        $mensajeError = $_SESSION['error_registro'];
        unset($_SESSION['error_registro']);
    }

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Hybox</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- CDN Sweet Alert 2 -->
</head>

<body>
    <form method="post" action="../../src/auth/sesion/registrar.php">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" placeholder="Nombre" required><br>

        <label for="apellidos">Apellidos</label>
        <input type="text" name="apellidos" placeholder="Apellidos" required><br><br>

        <label for="correo">Correo Electrónico</label>
        <input type="email" name="correo" placeholder="Correo electrónico" required><br>

        <label for="contraseña">Contraseña</label>
        <input type="password" name="contraseña" placeholder="Contraseña" required><br>
        <small style="color: gray;">La contraseña debe tener:</small>
        <ul class="validacion" style="margin-top: 2px; margin-bottom: 8px; padding-left: 20px; color: gray;">
            <li>Al menos 8 caracteres</li>
            <li>Una letra mayúscula</li>
            <li>Una letra minúscula</li>
            <li>Un número</li>
            <li>Un carácter especial (!@#$%^&*)</li>
        </ul>

        <label for="contraConfi">Confirmar Contraseña</label>
        <input type="password" name="contraConfi" placeholder="Confirmar contraseña" required><br>
        <button type="submit">Registrarse</button>
    </form>

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