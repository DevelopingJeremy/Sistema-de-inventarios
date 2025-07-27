<?php

    include_once('verificaciones-sesion.php');
    iniSesion();
    include('../../../config/db.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $correo = $_POST['correo'];
        $contraseña = $_POST['contraseña'];
        $contraConfi = $_POST['contraConfi'];
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $nombre_completo = $nombre . " " .$apellidos;

        // Función para validar contraseña fuerte
        function esContraseñaFuerte($contraseña) {
            return strlen($contraseña) >= 8 &&
                preg_match('/[A-Z]/', $contraseña) &&    // al menos una mayúscula
                preg_match('/[a-z]/', $contraseña) &&    // al menos una minúscula
                preg_match('/[0-9]/', $contraseña) &&    // al menos un número
                preg_match('/[\W_]/', $contraseña);      // al menos un carácter especial
        }

        // Verificar si ya existe un correo
        $stmt = $conn->prepare("SELECT * FROM t_usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si existe usuario
        if ($result && $result->num_rows > 0) {
            $_SESSION['error_registro'] = "Correo ya existente.";
            header("Location: ../../../public/sesion/registro.php");
            exit();
        } else {
            // Verificar que las contraseñas coincidan
            if ($contraseña == $contraConfi) {
                // Verificar que la contraseña sea fuerte
                if (esContraseñaFuerte($contraseña)) {
                    // Encriptar la contraseña
                    $hash = password_hash($contraseña, PASSWORD_DEFAULT);

                    // Preparar el insert
                    $stmt = $conn->prepare("INSERT INTO t_usuarios(correo, contraseña, nombre_completo) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $correo, $hash, $nombre_completo);

                    if ($stmt->execute()) {
                        // Solicitar ID del usuario
                        $stmt = $conn->prepare("SELECT ID_USUARIO FROM t_usuarios WHERE correo = ?");
                        $stmt->bind_param("s", $correo);
                        $stmt->execute();
                        $resultado = $stmt->get_result();
                        $fila = $resultado->fetch_assoc();
                        $idUsuario = $fila['ID_USUARIO'];

                        // Generar token y guardarlo
                        $token = bin2hex(random_bytes(32));

                        $stmt = $conn->prepare("INSERT INTO tokens(ID_USUARIO, token, usado) VALUES (?,?, 0)");
                        $stmt->bind_param("ss", $idUsuario, $token);
                        $stmt->execute();

                        // Guardar correo en la sesión
                        $_SESSION['correo'] = $correo;
                        $_SESSION['nombre_usuario'] = $nombre_completo;
                        $_SESSION['id_usuario'] = $idUsuario;
                        $_SESSION['token_correo'] = $token;
                        $_SESSION['correo_verificado'] = false;
                        $_SESSION['sesion'] = true;
                        header('Location: ../../mail/veri-email.php');
                        exit();
                    } else {
                        $_SESSION['error_registro'] = "Error al registrar el usuario, intente nuevamente.";
                        header("Location: ../../../public/sesion/registro.php");
                        exit();
                    }

                    $stmt->close(); // Cerrar el statement
                } else {
                    $_SESSION['error_registro'] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
                    header("Location: ../../../public/sesion/registro.php");
                    exit();
                }
            } else {
                $_SESSION['error_registro'] = "Las contraseñas no coinciden.";
                header("Location: ../../../public/sesion/registro.php");
                exit();
            }
        }

        $conn->close();
    }
?>