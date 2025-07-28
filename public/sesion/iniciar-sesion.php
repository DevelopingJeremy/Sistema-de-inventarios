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
    <title>Iniciar Sesión | Hybox</title>
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
            <p class="auth-subtitle">Bienvenido de vuelta</p>
            <p class="auth-description">Ingresa tus credenciales para acceder a tu cuenta</p>
        </div>

        <form method="post" action="../../src/auth/sesion/iniciar.php" class="auth-form">
            <div class="form-group">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" placeholder="tu@correo.com" required id="correo" class="form-input">
            </div>

            <div class="form-group">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" name="contraseña" placeholder="Tu contraseña" required id="contraseña" class="form-input">
            </div>

            <button type="submit" class="btn-submit" id="iniciarSesion">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>

        <div class="auth-links">
            <p>¿No tienes una cuenta? <a href="registro.php" class="auth-link">Regístrate aquí</a></p>
            <p><a href="cambiar-contraseña.php" class="auth-link">¿Olvidaste tu contraseña?</a></p>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades de autenticación
        document.addEventListener('DOMContentLoaded', function() {
            initAuthPage();
        });
        
        <?php if ($error): ?>
        // Mostrar error de autenticación
        document.addEventListener('DOMContentLoaded', function() {
            showAuthError("<?php echo $mensajeError ?>");
        });
        <?php endif; ?>
    </script>
</body>

</html>