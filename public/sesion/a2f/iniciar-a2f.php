<?php

    include('../../../config/db.php');
    require_once('../../../src/auth/sesion/verificaciones-sesion.php');
    iniSesion();
    inactividad('../../sesion/iniciar-sesion.php');
    
    // Verificar que el usuario tenga sesión básica iniciada
    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['correo'])) {
        header("Location: ../../sesion/iniciar-sesion.php");
        exit();
    }

    $error = false;
    $success = false;

    if (isset($_SESSION['error_a2f'])) {
        $error = true;
        $mensajeError = $_SESSION['error_a2f'];
        unset($_SESSION['error_a2f']);
    }

    if (isset($_SESSION['success_a2f'])) {
        $success = true;
        $mensajeSuccess = $_SESSION['success_a2f'];
        unset($_SESSION['success_a2f']);
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticación 2FA | Hybox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/auth-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="auth-container a2f-container">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-cube"></i>
                <h1>Hybox</h1>
            </div>
            <div class="verification-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2 class="verification-title">Autenticación en 2 Factores</h2>
            <p class="verification-text">
                Te acabamos de enviar un código de 4 dígitos a tu correo <span class="verification-email"><?php echo $_SESSION['correo']; ?></span>
            </p>
            <p class="verification-text" style="font-size: 0.9rem; color: var(--warning-color); margin-top: 8px;">
                <i class="fas fa-clock"></i> El código expira en 15 minutos
            </p>
        </div>

        <form action="../../../src/auth/a2f/procesar-a2f.php" method="post" class="auth-form">
            <div class="form-group">
                <label for="codigo" class="form-label">Código de Verificación</label>
                <input type="text" name="codigo" maxlength="4" required id="codigo" class="form-input a2f-code-input" placeholder="0000">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-check"></i> Verificar Código
            </button>
        </form>

        <div class="auth-links">
            <p>¿No ves tu código? Revisa la carpeta de SPAM o <a href="#" id="reenviar-a2f" class="auth-link">reenvíalo</a></p>
        </div>
    </div>
    
    <script>
        // Auto-focus en el campo de código
        document.getElementById('codigo').focus();
        
        // Auto-avance al siguiente campo (si fuera necesario)
        document.getElementById('codigo').addEventListener('input', function() {
            if (this.value.length === 4) {
                this.form.submit();
            }
        });

        // SweetAlert para reenviar código
        document.getElementById('reenviar-a2f').addEventListener('click', e => {
            e.preventDefault();
            
            // Mostrar loading
            Swal.fire({
                title: 'Enviando código...',
                text: 'Por favor espera mientras reenviamos el código',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('../../../src/auth/a2f/enviar-a2f.php')
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Código reenviado!',
                    text: 'El código de verificación ha sido enviado exitosamente a tu correo',
                    confirmButtonColor: '#007bff',
                    confirmButtonText: 'Entendido'
                });
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al reenviar',
                    text: 'No se pudo reenviar el código. Intenta nuevamente.',
                    confirmButtonColor: '#007bff',
                    confirmButtonText: 'Intentar de nuevo'
                });
            });
        });

        // Mostrar errores con SweetAlert
        <?php if ($error): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error de verificación',
            text: '<?php echo $mensajeError ?>',
            confirmButtonColor: '#007bff',
            confirmButtonText: 'Intentar de nuevo'
        });
        <?php endif; ?>

        // Mostrar mensajes de éxito con SweetAlert
        <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Verificación exitosa!',
            text: '<?php echo $mensajeSuccess ?>',
            confirmButtonColor: '#007bff',
            confirmButtonText: 'Continuar'
        });
        <?php endif; ?>
    </script>
</body>
</html>