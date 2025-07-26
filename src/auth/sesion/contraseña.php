<?php

    include('../../../config/db.php');
    require_once('../sesion/verificaciones-sesion.php');
    validTotales('../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/envio-correo.php', '../../../public/empresa/registrar-empresa.php');

    // Variables de Sweet Alert
    $guardado = false;
    $error = false;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Guardar datos del POST
        $contraVieja = trim($_POST['contra-vieja']);
        $contraNuevo = trim($_POST['contra-nueva']);
        $confirContra = trim($_POST['confir-contra']);
        $correo = $_SESSION['correo'];

        // Obtener contraseña actual para comparación
        $sql = $conn->prepare('SELECT * FROM t_usuarios WHERE correo = ?');
        $sql->bind_param("s", $correo);
        $sql->execute();
        $result = $sql->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verificar contraseña actual y escrita
            if (password_verify($contraVieja, $row['contraseña'])) {
                
                // Compara contraseñas nuevas
                if ($confirContra == $contraNuevo) {
                    $newPass = password_hash($contraNuevo, PASSWORD_DEFAULT);

                    $sql = $conn->prepare('UPDATE t_usuarios SET contraseña = ?');
                    $sql->bind_param("s", $newPass);
                    
                    if ($sql->execute()) {
                        $_SESSION['cambioPass'] = true;
                        header("Location: ../../../public/sesion/cambiar-contraseña.php");
                    }

                } else {
                    // Contraseña nuevas no coinciden
                    $_SESSION['error_cambioPass'] = "Las nuevas contraseñas no coinciden.";
                    header("Location: ../../../public/sesion/cambiar-contraseña.php");
                }

            } else {
                // Contraseña actual no coincide con la escrita
                $_SESSION['error_cambioPass'] = "La contraseña actual no coincide con la escrita.";
                header("Location: ../../../public/sesion/cambiar-contraseña.php");
            }

        } else {
            // Error al obtener resultado
            $_SESSION['error_cambioPass'] = "Error al cambiar las contraseñas, intente nuevamente.";
            header("Location: ../../../public/sesion/cambiar-contraseña.php");
        }

    }

?>