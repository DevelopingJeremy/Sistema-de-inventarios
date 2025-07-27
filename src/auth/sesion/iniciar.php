<?php
    include_once('verificaciones-sesion.php');
    iniSesion();
    include('../../../config/db.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $correo = $_POST['correo'];
        $contraseña = $_POST['contraseña'];

        // Validar formato del correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_login'] = "Correo inválido";
            header("Location: ../../../public/sesion/iniciar-sesion.php");
            exit();
        }

        // Preparar la consulta
        $stmt = $conn->prepare("SELECT * FROM t_usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si hay contenido
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verificar contraseña
            if (password_verify($contraseña, $row['contraseña'])) {
                // Guardar datos estandares en sesion
                $_SESSION['id_usuario'] = $row['ID_USUARIO'];
                $_SESSION['nombre_usuario'] = $row['nombre_completo'];
                $_SESSION['correo'] = $row['correo'];

                if ($row['correo_verifi'] == 0) {
                    // Obtener Token
                    $stmt = $conn->prepare("SELECT * FROM tokens WHERE ID_USUARIO = ? AND usado = 0");
                    $stmt->bind_param("i", $row['ID_USUARIO']);

                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    // Token existe y se encuentra
                    if ($resultado->num_rows == 1) {
                        $fila = $resultado->fetch_assoc();

                        // Guardar datos en sesión
                        $_SESSION['correo_verificado'] = false;
                        $_SESSION['token_correo'] = $fila['token'];
                        header("Location: ../../../public/sesion/envio-correo.php");
                        exit();
                    } else {
                        // Error al obtener los datos
                        $_SESSION['error_login'] = "Error al iniciar sesion, intente nuevamente.";
                        header("Location: ../../../public/sesion/iniciar-sesion.php");
                        exit();
                    }
                } else {
                    // Correo ya verificado
                    $_SESSION['correo_verificado'] = true;

                    // Corroborar si ya cuenta con empresa
                    if ($row['ID_EMPRESA'] != NULL) {
                        $_SESSION['empresa_creada'] = true;
                        // A2F activado
                        if ($row['a2f'] == 1) {
                            header("Location: ../a2f/enviar-a2f.php");
                            exit();
                        } else {
                            // A2F NO activado
                            $_SESSION['sesion'] = true;
                            header("Location: ../../../public/dashboard/dashboard.php");
                            exit();
                        }
                    } else {
                        // No tiene empresa registrada
                        $_SESSION['empresa_creada'] = false;
                        header('Location: ../../../public/empresa/registrar-empresa.php');
                        exit();
                    }
                }
            } else {
                // Contraseña incorrecta
                $_SESSION['error_login'] = "Correo o contraseña incorrectos.";
                header("Location: ../../../public/sesion/iniciar-sesion.php");
                exit();
            }
        } else {
            // Correo incorrecto
            $_SESSION['error_login'] = "Correo o contraseña incorrectos.";
            header("Location: ../../../public/sesion/iniciar-sesion.php");
            exit();
        }

        $stmt->close();
    }

    $conn->close();
?>