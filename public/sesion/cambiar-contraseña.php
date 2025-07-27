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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-cube"></i>
                <h1>Hybox</h1>
            </div>
            <p class="auth-subtitle">Cambiar contraseña</p>
            <p class="auth-description">Actualiza tu contraseña para mantener tu cuenta segura</p>
        </div>

        <form method="post" action="../../src/auth/sesion/contraseña.php" class="auth-form">
            <div class="form-group">
                <label for="contra-vieja" class="form-label">Contraseña Actual</label>
                <input type="password" name="contra-vieja" placeholder="Tu contraseña actual" required id="contra-vieja" class="form-input">
            </div>

            <div class="form-group">
                <label for="contra-nueva" class="form-label">Nueva Contraseña</label>
                <input type="password" name="contra-nueva" placeholder="Tu nueva contraseña" required id="contra-nueva" class="form-input">
                
                <div class="password-requirements">
                    <small>La nueva contraseña debe tener:</small>
                    <ul class="requirements-list">
                        <li>Al menos 8 caracteres</li>
                        <li>Una letra mayúscula</li>
                        <li>Una letra minúscula</li>
                        <li>Un número</li>
                        <li>Un carácter especial (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="confir-contra" class="form-label">Confirmar Nueva Contraseña</label>
                <input type="password" name="confir-contra" placeholder="Confirma tu nueva contraseña" required id="confir-contra" class="form-input">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-key"></i> Cambiar Contraseña
            </button>
        </form>

        <div class="auth-links">
            <p><a href="../dashboard/dashboard.php" class="auth-link">Volver al Dashboard</a></p>
        </div>
    </div>

    <?php if ($guardado): ?>
        <script>
            Swal.fire({
                position: "center",
                icon: "success",
                title: "¡Contraseña actualizada correctamente!",
                showConfirmButton: false,
                timer: 1500,
                confirmButtonColor: '#007bff'
            }).then(() => { window.location.href = "../dashboard/dashboard.php"; });
        </script>
    <?php endif; ?>

    
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: "error",
            title: "Error al cambiar contraseña",
            text: "<?php echo $mensajeError ?>",
            confirmButtonColor: '#007bff'
        });
    </script>
    <?php endif; ?>
</body>

</html>