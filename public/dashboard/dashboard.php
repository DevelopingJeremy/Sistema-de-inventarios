<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    // Mensajes desactivados de manera predeterminada
    $error = false;
    $exito = false;

    $nombre = $_SESSION['nombre_usuario'];
    echo "<h1>Bienvenido a la dashboard $nombre</h1>";

    // Activar PopUps
    if (isset($_SESSION['mensaje_exito'])) {
        $exito = true;
        $mensajeExito = $_SESSION['mensaje_exito'];
        unset($_SESSION['mensaje_exito']);
    }

    if (isset($_SESSION['mensaje_error'])) {
        $error = true;
        $mensajeError = $_SESSION['mensaje_error'];
        unset($_SESSION['mensaje_error']);
    }



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Hybox</title>
    <!-- CDN Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <a href="../../src/auth/sesion/salir.php">Cerrar Sesión</a><br>
    <a href="../../index.html">Inicio</a>



    <!-- PopUps -->

    <!-- Mensaje Exito -->
    <?php if($exito): ?>
    <script>
    Swal.fire({
        position: "center",
        icon: "success",
        title: "<?php echo $mensajeExito ?>",
        showConfirmButton: false,
        timer: 1500
    });
    </script>
    <?php endif; ?>


    <!-- Mensaje Error -->
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