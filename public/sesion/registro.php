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
            <p class="auth-subtitle">Crear nueva cuenta</p>
            <p class="auth-description">Completa el formulario para crear tu cuenta</p>
        </div>

        <form method="post" action="../../src/auth/sesion/registrar.php" class="auth-form">
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" placeholder="Tu nombre" required id="nombre" class="form-input">
            </div>

            <div class="form-group">
                <label for="apellidos" class="form-label">Apellidos</label>
                <input type="text" name="apellidos" placeholder="Tus apellidos" required id="apellidos" class="form-input">
            </div>

            <div class="form-group">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" placeholder="tu@correo.com" required id="correo" class="form-input">
            </div>

            <div class="form-group">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" name="contraseña" placeholder="Tu contraseña" required id="contraseña" class="form-input">
                
                <div class="password-requirements">
                    <small>La contraseña debe tener:</small>
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
                <label for="contraConfi" class="form-label">Confirmar Contraseña</label>
                <input type="password" name="contraConfi" placeholder="Confirma tu contraseña" required id="contraConfi" class="form-input">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i> Crear Cuenta
            </button>
        </form>

        <div class="auth-links">
            <p>¿Ya tienes una cuenta? <a href="iniciar-sesion.php" class="auth-link">Inicia sesión aquí</a></p>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        <?php if ($error): ?>
        // Mostrar error de registro
        document.addEventListener('DOMContentLoaded', function() {
            showAuthError("<?php echo $mensajeError ?>");
        });
        <?php endif; ?>
    </script>
</body>

</html>